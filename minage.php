<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loicoin</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['name'])) {
        header("Location: connect.php");
        exit();
    }

    $name = $_SESSION['name'];

    require('database.php');
    ?>

<div class="d-flex align-items-start">
  <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
    <button class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home" aria-selected="true">Mining</button>
    <button class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">Mempool</button>
    <button class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" data-bs-target="#v-pills-messages" type="button" role="tab" aria-controls="v-pills-messages" aria-selected="false">Messages</button>
    <button class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-settings" type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false">Settings</button>
  </div>
  <div class="tab-content" id="v-pills-tabContent">
    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
        <?php
        function simpleHash($data) {
            return hash('sha256', $data);
        }

        function calculateMerkleRoot($transactions) {
            if (count($transactions) == 1) {
                return $transactions[0];
            }

            $newLevel = [];
            for ($i = 0; $i < count($transactions); $i += 2) {
                if (isset($transactions[$i + 1])) {
                    $newLevel[] = simpleHash($transactions[$i] . $transactions[$i + 1]);
                } else {
                    $newLevel[] = simpleHash($transactions[$i] . $transactions[$i]);
                }
            }

            return calculateMerkleRoot($newLevel);
        }

        function proofOfWork($previousHash, $merkleRoot, $timestamp, $difficulty) {
            $nonce = 0;
            $target = str_repeat('0', $difficulty);

            while (true) {
                $blockData = $previousHash . $merkleRoot . $timestamp . $nonce;
                $hash = simpleHash($blockData);
                if (substr($hash, 0, $difficulty) === $target) {
                    return $nonce;
                }
                $nonce++;
            }
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupérer les transactions en attente
            $sql_transactions = "SELECT * FROM transaction WHERE statut = 'attente'";
            $result_transactions = $connect->query($sql_transactions);

            if ($result_transactions->num_rows == 0) {
                echo "<div class='container mt-5'>";
                echo "<h2>Erreur de Minage</h2>";
                echo "<p>Aucune transaction en attente. Veuillez vérifier le mempool.</p>";
                echo "</div>";
            } else {
                $transactions = [];
                $transaction_ids = [];
                while ($row = $result_transactions->fetch_assoc()) {
                    $transactionHash = simpleHash($row['sender'] . $row['receiver'] . $row['montant'] . $row['fee'] . $row['date']);
                    $transactions[] = $transactionHash;
                    $transaction_ids[] = $row['id_tra']; // Stocker les IDs des transactions
                }

                // Calculer le Merkle Root
                $merkleRoot = calculateMerkleRoot($transactions);

                // Récupérer les données du dernier bloc
                $sql_last_block = "SELECT hash_precedent, timestamp FROM bloc ORDER BY id_bloc DESC LIMIT 1";
                $result_last_block = $connect->query($sql_last_block);
                $row_last_block = $result_last_block->fetch_assoc();

                if ($row_last_block) {
                    $previousHash = $row_last_block['hash_precedent'];
                    $timestamp = $row_last_block['timestamp'];
                } else {
                    // Si aucun bloc n'existe, utiliser des valeurs par défaut
                    $previousHash = "0000000000000000000000000000000000000000000000000000000000000000";
                    $timestamp = time();
                }

                $difficulty = 4; // Nombre de zéros requis au début du hash

                // Trouver le nonce
                $nonce = proofOfWork($previousHash, $merkleRoot, $timestamp, $difficulty);
                $blockHash = simpleHash($previousHash . $merkleRoot . $timestamp . $nonce);

                // Mettre à jour le statut des transactions minées
                $sql_update_transactions = "UPDATE transaction SET statut = 'valide' WHERE id_tra IN (" . implode(',', $transaction_ids) . ")";
                $connect->query($sql_update_transactions);

                echo "<div class='container mt-5'>";
                echo "<h2>Résultat du Minage</h2>";
                echo "<p>Merkle Root: $merkleRoot</p>";
                echo "<p>Nonce: $nonce</p>";
                echo "<p>timestamp: $timestamp</p>";
                echo "<p>Hash du Bloc: $blockHash</p>";
                echo "</div>";
            }
        }
        ?>

        <div class="container mt-5">
            <h2>Minage de BTC</h2>
            <form action="#" method="POST">
                <button type="submit" class="btn btn-primary">Commencer le Minage</button>
            </form>
        </div>
    </div>
    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
        <?php
        // Récupérer les transactions
        $sql = "SELECT * FROM transaction ORDER BY id_tra DESC";
        $result = $connect->query($sql);
        ?>

        <div class="container mt-5">
            <h2>Historique des Transactions</h2>
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Expéditeur</th>
                            <th>Destinataire</th>
                            <th>Montant (BTC)</th>
                            <th>Frais (BTC)</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id_tra']); ?></td>
                                <td><?php echo htmlspecialchars($row['sender']); ?></td>
                                <td><?php echo htmlspecialchars($row['receiver']); ?></td>
                                <td><?php echo htmlspecialchars($row['montant']); ?></td>
                                <td><?php echo htmlspecialchars($row['fee']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['statut']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune transaction trouvée.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">michou</div>
    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">...</div>
  </div>
</div>
</body>
</html>
