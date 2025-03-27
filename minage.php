<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loicoin</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="minage.css">
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

    // Récupérer le terme de recherche
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    ?>
    
<div class="d-flex align-items-start">
  <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
    <button class="nav-link" id="v-pills-mining-tab" data-bs-toggle="pill" data-bs-target="#v-pills-mining" type="button" role="tab" aria-controls="v-pills-mining" aria-selected="false">Mining</button>
    <button class="nav-link" id="v-pills-mempool-tab" data-bs-toggle="pill" data-bs-target="#v-pills-mempool" type="button" role="tab" aria-controls="v-pills-mempool" aria-selected="false">Mempool</button>
    <button class="nav-link active" id="v-pills-blocks-tab" data-bs-toggle="pill" data-bs-target="#v-pills-blocks" type="button" role="tab" aria-controls="v-pills-blocks" aria-selected="true">Blocs</button>
    <button class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-settings" type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false">Settings</button>
  </div>
  
  <div class="tab-content" id="v-pills-tabContent">
    <!-- Onglet Mining -->
    <div class="tab-pane fade" id="v-pills-mining" role="tabpanel" aria-labelledby="v-pills-mining-tab">
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

        //modifie à partir d'ici
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mining_start'])) {
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
                     $transaction_ids[] = $row['id_tra'];
                 }
 
                 $merkleRoot = calculateMerkleRoot($transactions);
 
                 $sql_last_block = "SELECT hash_bloc, timestamp FROM bloc ORDER BY id_bloc DESC LIMIT 1";
                 $result_last_block = $connect->query($sql_last_block);
                 $row_last_block = $result_last_block->fetch_assoc();
 
                 if ($row_last_block) {
                     $previousHash = $row_last_block['hash_bloc'];
                     $timestamp = $row_last_block['timestamp'];
                 } 
 
                 $difficulty = 4;
                 $nonce = proofOfWork($previousHash, $merkleRoot, $timestamp, $difficulty);
                 $blockHash = simpleHash($previousHash . $merkleRoot . $timestamp . $nonce);
 
                 // Mettre à jour les transactions
                 $sql_update_transactions = "UPDATE transaction SET statut = 'valide' WHERE id_tra IN (" . implode(',', $transaction_ids) . ")";
                 $connect->query($sql_update_transactions);
 
                 // Insérer le nouveau bloc
                 $count_transaction = count($transaction_ids);
                 $timestamp2 = date('Y-m-d H:i:s');
                 $sql_insert_block = "INSERT INTO bloc (hash_bloc, hash_precedent, nonce, difficulte, merkle_root, timestamp, nbre_transaction, mineur) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                 $stmt_insert_block = $connect->prepare($sql_insert_block);
                 $stmt_insert_block->bind_param("ssiissii", $blockHash, $previousHash, $nonce, $difficulty, $merkleRoot, $timestamp2, $count_transaction, $name);
                 $stmt_insert_block->execute();
 
                 echo "<div class='container mt-5'>";
                 echo "<h2>Résultat du Minage</h2>";
                 echo "<p>Merkle Root: $merkleRoot</p>";
                 echo "<p>Nonce: $nonce</p>";
                 echo "<p>Hash du Bloc: $blockHash</p>";
                 echo "</div>";
             }
         }
         ?>
        <div class="container mt-5">
            <h2>Minage de BTC</h2>
            <form method="POST" action="#">
                <button type="submit" name="mining_start" class="btn btn-primary">Commencer le Minage</button>
            </form>
        </div>
    </div>
    <!-- jusque ici-->
    
    <!-- Onglet Mempool -->
    <div class="tab-pane fade" id="v-pills-mempool" role="tabpanel" aria-labelledby="v-pills-mempool-tab">
    <div class="container mt-4">
        <h2 class="mb-4">Mempool des Transactions</h2>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Statistiques</h5>
                        <?php
                        $sql_stats = "SELECT 
                                     COUNT(*) as total_transactions,
                                     SUM(montant) as total_amount,
                                     SUM(fee) as total_fees
                                     FROM transaction WHERE statut = 'attente'";
                        $stats = $connect->query($sql_stats)->fetch_assoc();
                        ?>
                        <p class="mb-1">Transactions en attente: <strong><?= $stats['total_transactions'] ?></strong></p>                    </div>
                </div>
            </div>

        </div>

        <div class="mempool-container">
            <?php
            $sql = "SELECT * FROM transaction ORDER BY id_tra DESC";
            $result = $connect->query($sql);

            if ($result->num_rows > 0): ?>
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Transactions en attente de confirmation</h5>
                            <span class="badge bg-primary"><?= $result->num_rows ?> transactions</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Expéditeur</th>
                                        <th>Destinataire</th>
                                        <th>Montant</th>
                                        <th>Frais</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): 
                                        $status_class = $row['statut'] === 'valide' ? 'bg-success' : ($row['statut'] === 'attente' ? 'bg-warning text-dark' : 'bg-danger');
                                    ?>
                                    <tr class="transaction-row">
                                        <td class="text-muted"><?= substr($row['id_tra'], 0, 8) ?>..</td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 100px;">
                                                <?= htmlspecialchars($row['sender']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 100px;">
                                                <?= htmlspecialchars($row['receiver']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold"><?= number_format($row['montant'], 8) ?> BTC</td>
                                        <td class="text-muted"><?= number_format($row['fee'], 8) ?> BTC</td>
                                        <td><?= date('H:i', strtotime($row['date'])) ?></td>
                                        <td><span class="badge <?= $status_class ?>"><?= htmlspecialchars($row['statut']) ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="mt-3">Mempool vide</h5>
                        <p class="text-muted">Aucune transaction en attente de confirmation</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    <!-- Onglet Blocs -->
    <div class="tab-pane fade show active" id="v-pills-blocks" role="tabpanel" aria-labelledby="v-pills-blocks-tab">
        <div class="container mt-4">
            <h2 class="mb-4">Explorateur de Blockchain</h2>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Statistiques</h5>
                            <?php
                            $sql_stats = "SELECT 
                                         COUNT(*) as total_blocks, 
                                         SUM(nbre_transaction) as total_transactions,
                                         MAX(timestamp) as last_block_time
                                         FROM bloc";
                            $stats = $connect->query($sql_stats)->fetch_assoc();
                            ?>
                            <p class="mb-1">Hauteur: <strong><?= $stats['total_blocks'] ?></strong></p>
                            <p class="mb-1">Transactions: <strong><?= $stats['total_transactions'] ?></strong></p>
                            <p class="mb-0">Dernier bloc: <strong><?= date('H:i:s', strtotime($stats['last_block_time'])) ?></strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Recherche</h5>
                            <form method="GET" class="row g-2">
                                <input type="hidden" name="tab" value="blocks">
                                <div class="col-md-8">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           placeholder="Rechercher par ID de bloc, hash de bloc ou mineur..."
                                           value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="blockchain-container">
                <?php
                // Construction de la requête SQL
                $sql_blocs = "SELECT * FROM bloc";
                $where = [];
                $params = [];
                $types = '';
                
                if (!empty($search)) {
                    // Recherche par ID de bloc (nombre)
                    if (is_numeric($search)) {
                        $where[] = "id_bloc = ?";
                        $params[] = $search;
                        $types .= 'i';
                    }
                    // Recherche par hash (64 caractères hexadécimaux)
                    elseif (preg_match('/^[a-f0-9]{64}$/i', $search)) {
                        $where[] = "(hash_bloc = ? OR hash_precedent = ? OR merkle_root = ?)";
                        array_push($params, $search, $search, $search);
                        $types .= 'sss';
                    }
                    // Recherche par nom de mineur
                    else {
                        $where[] = "mineur LIKE ?";
                        $params[] = "%$search%";
                        $types .= 's';
                    }
                    
                    if (!empty($where)) {
                        $sql_blocs .= " WHERE " . implode(" OR ", $where);
                    }
                }
                
                $sql_blocs .= " ORDER BY id_bloc DESC LIMIT 15";
                
                // Préparation de la requête
                $stmt = $connect->prepare($sql_blocs);
                
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                
                $stmt->execute();
                $result_blocs = $stmt->get_result();
                
                if ($result_blocs->num_rows > 0):
                    while ($bloc = $result_blocs->fetch_assoc()):
                        // Récupérer les transactions du bloc
                        $sql_tx = "SELECT * FROM transaction WHERE statut = 'valide' LIMIT 3";
                        $result_tx = $connect->query($sql_tx);
                        $tx_count = $result_tx->num_rows;
                ?>
                <div class="block-card mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-secondary me-2">Bloc #<?= $bloc['id_bloc'] ?></span>
                                <small class="text-muted"><?= date('d/m/Y H:i:s', strtotime($bloc['timestamp'])) ?></small>
                            </div>
                            <div>
                                <span class="badge bg-primary"><?= $bloc['nbre_transaction'] ?> tx</span>
                                <span class="badge bg-info ms-1"><?= $bloc['difficulte'] ?> diff</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Hash du bloc</h6>
                                        <p class="hash-value"><?= substr($bloc['hash_bloc'], 0, 20) ?>...<?= substr($bloc['hash_bloc'], -20) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Hash précédent</h6>
                                        <p class="hash-value"><?= substr($bloc['hash_precedent'], 0, 20) ?>...<?= substr($bloc['hash_precedent'], -20) ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Mineur</h6>
                                        <p><?= $bloc['mineur'] ?></p>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <h6 class="text-muted mb-1">Nonce</h6>
                                            <p><?= $bloc['nonce'] ?></p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-muted mb-1">Merkle Root</h6>
                                            <p class="hash-value"><?= substr($bloc['merkle_root'], 0, 12) ?>...<?= substr($bloc['merkle_root'], -12) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Transactions du bloc -->
                            <div class="transactions-preview mt-3">
                                <h6 class="text-muted mb-2">Transactions</h6>
                                <?php if ($tx_count > 0): ?>
                                    <div class="list-group">
                                        <?php while ($tx = $result_tx->fetch_assoc()): ?>
                                        <div class="list-group-item list-group-item-action small py-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-truncate" style="max-width: 150px;">
                                                    <?= substr($tx['sender'], 0, 8) ?>... → <?= substr($tx['receiver'], 0, 8) ?>...
                                                </span>
                                                <span class="text-nowrap"><?= $tx['montant'] ?> BTC</span>
                                            </div>
                                            <div class="text-muted small"><?= substr($tx['id_tra'], 0, 12) ?>...</div>
                                        </div>
                                        <?php endwhile; ?>
                                        <?php if ($bloc['nbre_transaction'] > 3): ?>
                                            <div class="list-group-item small text-center py-2">
                                                + <?= $bloc['nbre_transaction'] - 3 ?> autres transactions
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-light mb-0">Aucune transaction dans ce bloc</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Confirmations: 12</small>
                                <a href="#" class="btn btn-sm btn-outline-primary">Voir les détails</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lien visuel entre les blocs -->
                    
                </div>
                <?php
                    endwhile;
                else:
                    if (!empty($search)) {
                        echo '<div class="alert alert-warning">Aucun bloc trouvé pour "' . htmlspecialchars($search) . '"</div>';
                    } else {
                        echo '<div class="alert alert-info">Aucun bloc trouvé dans la blockchain.</div>';
                    }
                endif;
                ?>
                
                <!-- Pagination -->
                <nav aria-label="Navigation des blocs">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Précédent</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Onglet Settings -->
    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
        <div class="container mt-5">
            <h2>Paramètres</h2>
            <p>Contenu des paramètres à venir...</p>
        </div>
    </div>
  </div>
</div>
</body>
</html>