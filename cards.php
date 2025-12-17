<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>

<body>
    <div class="modal-content">
        <div class="modal-header">
            <h2>➕ Ajouter une carte</h2>
        </div>

        <form method="POST" action="add_card.php">
            <div class="form-group">
                <label>Nom de la carte *</label>
                <input type="text" name="card_name" placeholder="Ex: Banque Pop Perso" required>
            </div>

            <div class="form-group">
                <label>Banque *</label>
                <select name="card_name" required>
                    <option value="">Choisir une banque</option>
                    <option value="Attijariwafa Bank">Attijariwafa Bank</option>
                    <option value="Banque Populaire">Banque Populaire</option>
                    <option value="BMCE Bank">BMCE Bank</option>
                    <option value="BMCI">BMCI</option>
                    <option value="CIH Bank">CIH Bank</option>
                    <option value="Crédit Agricole">Crédit Agricole du Maroc</option>
                    <option value="Crédit du Maroc">Crédit du Maroc</option>
                    <option value="Société Générale">Société Générale</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label>Solde initial (DH)</label>
                <input type="number" name="initial_balance" placeholder="0" step="0.01" value="0">
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 20px;">
                ✅ Ajouter la carte
            </button>
        </form>
    </div>
    </div>
</body>

</html>