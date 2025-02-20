<?php
session_start();
require_once '../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $required_fields = ['customer_id', 'vehicle_id', 'transaction_type', 'price', 'payment_method', 'payment_type'];
    $errors = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
        }
    }

    if (empty($errors)) {
        $pdo->beginTransaction();

        try {
            // Générer le numéro de facture
            $invoice_number = 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Préparer les données de base de la transaction
            $data = [
                'customer_id' => $_POST['customer_id'],
                'vehicle_id' => $_POST['vehicle_id'],
                'user_id' => $_SESSION['user_id'],
                'transaction_type' => $_POST['transaction_type'],
                'price' => $_POST['price'],
                'payment_method' => $_POST['payment_method'],
                'payment_type' => $_POST['payment_type'],
                'invoice_number' => $invoice_number,
                'notes' => $_POST['notes'] ?? null,
                'status' => 'completed'
            ];

            // Vérifier si le véhicule est disponible
            if ($data['transaction_type'] === 'sale') {
                $stmt = $pdo->prepare("SELECT status FROM vehicles WHERE id = ?");
                $stmt->execute([$data['vehicle_id']]);
                $vehicle_status = $stmt->fetchColumn();

                if ($vehicle_status !== 'available') {
                    throw new Exception("Ce véhicule n'est plus disponible à la vente");
                }
            }

            // Traitement du paiement mensuel
            if ($_POST['payment_type'] === 'monthly') {
                $data['installments'] = $_POST['installments'];
                $data['first_payment_date'] = $_POST['first_payment_date'];
                $down_payment = floatval($_POST['down_payment']);
                $remaining_amount = $data['price'] - $down_payment;
            }

            // Insertion de la transaction
            $sql = "INSERT INTO transactions (customer_id, vehicle_id, user_id, transaction_type, 
                    price, payment_method, payment_type, invoice_number, notes, status";
            
            $values = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
            $params = [
                $data['customer_id'], $data['vehicle_id'], $data['user_id'],
                $data['transaction_type'], $data['price'], $data['payment_method'],
                $data['payment_type'], $data['invoice_number'], $data['notes'],
                $data['status']
            ];

            if ($_POST['payment_type'] === 'monthly') {
                $sql .= ", installments, first_payment_date";
                $values .= ", ?, ?";
                $params[] = $data['installments'];
                $params[] = $data['first_payment_date'];
            }

            $sql .= ") VALUES " . $values . ")";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $transaction_id = $pdo->lastInsertId();

            // Gestion des paiements
            if (isset($down_payment) && $down_payment > 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO payments (transaction_id, amount, payment_date, status, 
                                       payment_method, payment_type)
                    VALUES (?, ?, CURRENT_DATE(), 'paid', ?, 'down_payment')
                ");
                $stmt->execute([$transaction_id, $down_payment, $data['payment_method']]);
            }

            // Création des mensualités
            if (isset($remaining_amount) && $remaining_amount > 0) {
                $monthly_amount = $remaining_amount / $data['installments'];
                $payment_date = new DateTime($data['first_payment_date']);

                for ($i = 0; $i < $data['installments']; $i++) {
                    $stmt = $pdo->prepare("
                        INSERT INTO payments (transaction_id, amount, payment_date, status,
                                           payment_method, payment_type)
                        VALUES (?, ?, ?, 'pending', ?, 'installment')
                    ");
                    $stmt->execute([
                        $transaction_id,
                        $monthly_amount,
                        $payment_date->format('Y-m-d'),
                        $data['payment_method']
                    ]);
                    
                    $payment_date->modify('+1 month');
                }
            }

            // Mise à jour du statut du véhicule
            $new_status = $data['transaction_type'] === 'sale' ? 'sold' : 'available';
            $stmt = $pdo->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $data['vehicle_id']]);

            // Gestion des documents
            if (!empty($_FILES['documents']['name'][0])) {
                $upload_dir = '../uploads/transactions/' . $transaction_id . '/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = uniqid() . '.' . pathinfo($_FILES['documents']['name'][$key], PATHINFO_EXTENSION);
                        $file_path = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($tmp_name, $file_path)) {
                            $stmt = $pdo->prepare("
                                INSERT INTO documents (transaction_id, document_type, file_name, file_path)
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $transaction_id,
                                $_POST['document_types'][$key],
                                $file_name,
                                '/uploads/transactions/' . $transaction_id . '/' . $file_name
                            ]);
                        }
                    }
                }
            }

            $pdo->commit();
            $_SESSION['success'] = "Transaction enregistrée avec succès";
            header('Location: /transactions/');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
            header('Location: /transactions/add.php');
            exit;
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header('Location: /transactions/add.php');
        exit;
    }
} 