<?php
require_once 'autoload.php';

use Laudis\Neo4j\ClientBuilder;

$client = ClientBuilder::create()
    ->withDriver('default', 'bolt://neo4j:password@localhost:7687')
    ->build();

$query = "MATCH (s:Supplier) RETURN s.companyName AS CompanyName";
$results = $client->run($query);

$companyNames = [];
foreach ($results as $result) {
    $companyNames[] = $result->get('CompanyName');
}

if (isset($_POST['company'])) {
    $selectedCompany = $_POST['company'];

    $query = "
    MATCH (s1:Supplier)-->()-->()<--()<--(s2:Supplier)
    WHERE s1.companyName = \$selectedCompany AND s1 <> s2
    RETURN s2.companyName as Competitor, count(s2) as NoProducts
    ORDER BY NoProducts DESC
    ";
    $results = $client->run($query, ['selectedCompany' => $selectedCompany]);

    $competitors = [];
    foreach ($results as $record) {
        $competitors[] = [
            'Competitor' => $record->get('Competitor'),
            'NoProducts' => $record->get('NoProducts')
        ];
    }

    echo json_encode($competitors);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Week 4 Neo4J</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        select {
            margin-top: 10px;
        }

        input {
            margin-top: 10px;
        }

        .table-container {
            overflow-x: auto;
        }

        .table th,
        .table td {
            text-align: center;
        }

        .table th {
            background-color: #343a40;
            color: white;
        }

        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table tr:hover {
            background-color: #ddd;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-left">Competitors List</h1>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-5">
                <label for="company">
                    <h5>Competitors Company Name</h5>
                </label>
                <select class="form-control" name="company" id="company">
                    <option value="">Choose Competitors...</option>
                    <?php foreach ($companyNames as $companyName) : ?>
                        <option value="<?= $companyName ?>"><?= $companyName ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <div class="table-container">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Competitor</th>
                                    <th>No Products</th>
                                </tr>
                            </thead>
                            <tbody id="restaurantTable">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            function loadCompetitors() {
                var selectedCompany = $('#company').val();

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: 'c14210250_week4.php',
                    data: {
                        company: selectedCompany
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        $('#restaurantTable').empty();

                        data.forEach(function(competitor) {
                            var row = '<tr>' +
                                '<td>' + competitor.Competitor + '</td>' +
                                '<td>' + competitor.NoProducts + '</td>' +
                                '</tr>';
                            $('#restaurantTable').append(row);
                            Swal.close();
                        });
                    }
                });
            }

            $('#company').on('change', loadCompetitors);
        });
    </script>
</body>

</html>