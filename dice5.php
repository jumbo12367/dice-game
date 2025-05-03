<?php
session_start();

// Initialize balance and history
if (!isset($_SESSION['balance'])) $_SESSION['balance'] = 0;
if (!isset($_SESSION['history'])) $_SESSION['history'] = [];

$message = "";
$dice = [];
$winAmount = 0;
$diceImages = ["img/dice1.png", "img/dice2.png", "img/dice3.png", "img/dice4.png", "img/dice5.png", "img/dice6.png"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_money'])) {
        $_SESSION['balance'] += intval($_POST['amount']);
    }

    if (isset($_POST['roll'])) {
        $bet = intval($_POST['bet']);
        $guess = [$_POST['guess1'], $_POST['guess2'], $_POST['guess3']];

        if ($bet <= 0 || $bet > $_SESSION['balance']) {
            $message = "Invalid or insufficient bet amount!";
        } elseif (!in_array($guess[0], range(1,6)) || !in_array($guess[1], range(1,6)) || !in_array($guess[2], range(1,6))) {
            $message = "Guesses must be 1–6 only.";
        } else {
            $dice = [rand(1,6), rand(1,6), rand(1,6)];
            $matches = 0;
            $tempDice = $dice; // copy to track matched dice

        foreach ($guess as $g) {
        $key = array_search($g, $tempDice);
            if ($key !== false) {
            $matches++;
            }     
}


            $_SESSION['balance'] -= $bet;

            if ($matches > 0) {
                $winAmount = $matches * $bet;
                $_SESSION['balance'] += $winAmount + $bet;
                $message = "Congratulations! You won ₱$winAmount";
                $result = "Win";
            } else {
                $message = "Nice try! You lost.";
                $result = "Loss";
            }            

            $_SESSION['history'][] = [
                "guess" => $guess,
                "dice" => $dice,
                "result" => $result,
                "win" => $winAmount
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dice Betting Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dice-img { width: 60px; height: 60px; }
        .guess-box { width: 60px; margin-top: 10px; text-align: center; }
        .history-box { max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <h3 class="text-center">₱ <span class="border px-3 py-1"><?php echo $_SESSION['balance']; ?></span>
        <button class="btn btn-success btn-sm" onclick="document.getElementById('add-form').classList.toggle('d-none')">+</button>
    </h3>

    <div id="add-form" class="text-center my-2 d-none">
        <form method="post" class="d-inline-block">
            <input type="number" name="amount" class="form-control d-inline-block w-50" placeholder="Add amount" required>
            <button type="submit" name="add_money" class="btn btn-primary btn-sm">Add</button>
        </form>
    </div>

    <div class="card shadow p-4 mt-4">
        <form method="post">
            <div class="row text-center mb-3">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="col">
                        <?php
                        if (!empty($dice)) {
                            echo "<img src='" . $diceImages[$dice[$i]-1] . "' class='dice-img'>";
                        } else {
                            echo "<img src='img/dice-1.png' class='dice-img opacity-25'>";
                        }
                        ?>
                        <input type="number" name="guess<?= $i+1 ?>" class="form-control guess-box" min="1" max="6" required>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="mb-3">
                <label>BET: ₱</label>
                <input type="number" name="bet" min="1" class="form-control d-inline-block w-50" required>
            </div>

            <button type="submit" name="roll" class="btn btn-primary w-100" onclick="playRoll()">ROLL</button>
        </form>

        <p class="text-center mt-3 fw-bold"><?= $message ?></p>
    </div>

    <!-- History -->
    <div class="card mt-4 history-box p-3">
        <h5>Game History</h5>
        <?php foreach (array_reverse($_SESSION['history']) as $entry): ?>
            <div class="small border-bottom py-2">
                <div><strong>Guess:</strong> <?= implode(", ", $entry['guess']) ?></div>
                <div><strong>Dice:</strong> <?= implode(", ", $entry['dice']) ?></div>
                <div><strong>Result:</strong> <?= $entry['result'] ?>, <strong>Winnings:</strong> ₱<?= $entry['win'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Sounds -->
<audio id="rollSound" src="sounds/roll.mp3"></audio>
<audio id="winSound" src="sounds/win.mp3"></audio>
<audio id="loseSound" src="sounds/lose.mp3"></audio>

<script>
    function playRoll() {
        document.getElementById("rollSound").play();
        setTimeout(() => {
            const msg = "<?= $message ?>";
            if (msg.includes("won")) document.getElementById("winSound").play();
            else if (msg.includes("lost")) document.getElementById("loseSound").play();
        }, 1000);
    }
</script>

</body>
</html>
