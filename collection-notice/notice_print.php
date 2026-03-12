<?php
require "config/db.php";
require "notice_logic.php";

if (!isset($_GET['id'])) {
    die("Invalid notice ID.");
}

$id = intval($_GET['id']);

$stmt = $mysqli->prepare("SELECT * FROM notices WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$notice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$notice) {
    die("Notice not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Collection Notice</title>

<style>
@media print {
    body { margin: 1in; }
}

body {
    font-family: "Times New Roman", serif;
    color: #000;
}

.header {
    text-align: center;
    margin-bottom: 25px;
}

.header img {
    height: 70px;
}

.right { text-align: right; }
.bold { font-weight: bold; }

.notice-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.notice-table th,
.notice-table td {
    border: 1px solid #000;
    padding: 6px;
    font-size: 14px;
    text-align: center;
}

.footer {
    margin-top: 50px;
}

.signature-box {
    margin-top: 25px;
}

.signature-box img {
    height: 60px;
}

.notice-level {
    margin-top: 30px;
    font-weight: bold;
}
</style>
</head>

<body onload="window.print()">

<!-- LETTERHEAD -->
<div class="header">
    <img src="assets/img/bank_logo.png"><br>
    <span class="bold">AGRIBUSINESS BANKING CORPORATION – A RURAL BANK</span><br>
    CAUAYAN BRANCH<br>
    LCU BUILDING, DISTRICT II CAUAYAN CITY, ISABELA
</div>

<!-- DATE -->
<div class="right">
    <?= date("F d, Y", strtotime($notice['letter_date'])) ?>
</div>

<br>

<!-- CLIENT INFO -->
<p class="bold">
Client ID: <?= htmlspecialchars($notice['client_id']) ?>
</p>

<p>
Loan Type: <?= htmlspecialchars($notice['loan_type']) ?><br>
Promissory Note No.: <?= htmlspecialchars($notice['pn_no']) ?><br>
Reference No.: <?= htmlspecialchars($notice['reference_no']) ?>
</p>

<br>

<!-- BODY -->
<p>
This refers to your <?= htmlspecialchars($notice['loan_type']) ?> with
Agribusiness Banking Corporation, Cauayan Branch covered by Promissory
Note No. <?= htmlspecialchars($notice['pn_no']) ?>.
</p>

<p>
As per record, it shows that your loan obligation remains unpaid as of
<?= date("F d, Y", strtotime($notice['as_of_date'])) ?> and has already
accumulated to
<strong>
<?= htmlspecialchars($notice['grand_total_words']) ?>
(₱<?= number_format($notice['grand_total'], 2) ?>)
</strong>
including penalties.
</p>

<!-- TOTALS -->
<table class="notice-table">
<thead>
<tr>
    <th>Total Amortization</th>
    <th>Total Interest</th>
    <th>Total Penalty</th>
    <th>Grand Total</th>
</tr>
</thead>
<tbody>
<tr>
    <td><?= number_format($notice['total_amort'], 2) ?></td>
    <td><?= number_format($notice['total_interest'], 2) ?></td>
    <td><?= number_format($notice['total_penalty'], 2) ?></td>
    <td class="bold"><?= number_format($notice['grand_total'], 2) ?></td>
</tr>
</tbody>
</table>

<br>

<p>
We urge you to please come and settle your obligation to the Bank within
<?= intval($notice['grace_days']) ?> day(s) upon receipt of this letter to
avoid further accumulation of interest and penalties and possible legal action.
</p>

<p>
We will appreciate your prompt settlement of your obligation.
</p>

<!-- SIGNATORY WITH DIGITAL SIGNATURE -->
<div class="footer">

<p class="bold">Very truly yours,</p>

<div class="signature-box">
<?php if (!empty($notice['signatory_signature']) && file_exists($notice['signatory_signature'])): ?>
    <img src="<?= htmlspecialchars($notice['signatory_signature']) ?>">
<?php else: ?>
    <br><br>
<?php endif; ?>
</div>

<p class="bold"><?= htmlspecialchars($notice['signatory_name']) ?></p>
<p><?= htmlspecialchars($notice['signatory_position']) ?></p>

</div>

<!-- NOTICE LEVEL -->
<p class="notice-level">
<?php if ($notice['notice_level'] === 'FINAL'): ?>
    FINAL DEMAND LETTER
<?php else: ?>
    THIS IS OUR <?= $notice['notice_level']; ?> LETTER
    (<?= $notice['notice_level']; ?> NOTICE)
<?php endif; ?>
</p>

<p>Please disregard this notice if payment has been made.</p>

<!-- ACKNOWLEDGMENT -->
<hr>

<p class="bold">ACKNOWLEDGMENT</p>

<p>
For the account of : _______________________________<br>
Amount Due : _______________________________<br>
Date Received : _______________________________<br>
Received By : _______________________________<br>
Relationship to Borrower : _____________________
</p>

</body>
</html>