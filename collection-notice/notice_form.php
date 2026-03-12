<!DOCTYPE html>
<html>
<head>
<title>Collection Notice</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<form action="save_notice.php" method="POST">

<h2>Collection Notice Entry</h2>

Borrower Name
<input name="borrower" required>

Address
<textarea name="address"></textarea>

Loan Type
<input name="loan_type" value="Pangkabuhayan Loan">

Promissory Note No.
<input name="pn">

Notice Date
<input type="date" name="notice_date">

As of Date
<input type="date" name="as_of_date">

Penalty Rate (% per day)
<input type="number" name="penalty_rate" value="1">

<h3>Amortizations</h3>

<table id="amortTable">
<tr>
<th>Due Date</th>
<th>Amortization</th>
<th>Interest</th>
</tr>
<tr>
<td><input type="date" name="due[]"></td>
<td><input name="amort[]"></td>
<td><input name="interest[]"></td>
</tr>
</table>

<button type="button" onclick="addRow()">Add Row</button>
<br><br>

<button type="submit">Save & Generate</button>

</form>

<script>
function addRow(){
  let r=document.getElementById("amortTable").insertRow();
  r.innerHTML=`<td><input type="date" name="due[]"></td>
               <td><input name="amort[]"></td>
               <td><input name="interest[]"></td>`;
}
</script>

</body>
</html>