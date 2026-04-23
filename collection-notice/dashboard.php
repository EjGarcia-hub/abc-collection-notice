<?php
// dashboard.php
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/app.php";
require_login();

$user = $_SESSION['user'] ?? [];
$APP_BASE = base_url();

/**
 * Branch code expected: "CAUAYAN" or "SANTIAGO"
 */
$branch = strtoupper(trim($user['branch_code'] ?? 'CAUAYAN'));
if (strpos($branch, 'SANTIAGO') !== false) $branch = 'SANTIAGO';
if (strpos($branch, 'CAUAYAN') !== false)  $branch = 'CAUAYAN';

/* =========================================
   DEFAULTS
   ========================================= */
if ($branch === 'SANTIAGO') {
  $branch_name = "SANTIAGO BRANCH";
  $branch_addr = "NATIONAL ROAD, PLARIDEL,<br>SANTIAGO CITY, ISABELA";
  $branch_sig  = "assets/signatures/santiago.png";
  $sign_name   = "PAULINO VEA CAUILAN";
  $sign_pos    = "BRANCH MANAGER";
} else {
  $branch_name = "CAUAYAN BRANCH";
  $branch_addr = "LCU BLDG. NATIONAL HIGHWAY, CABARUAN, DISTRICT 2,<br>CAUAYAN CITY, ISABELA 3305";
  $branch_sig  = "assets/signatures/cauayan.png";
  $sign_name   = "JOSE ESMUNDO GALAPON";
  $sign_pos    = "BRANCH MANAGER";
}

/* =========================================
   FETCH BRANCH SETTINGS FROM DB
   ========================================= */
try {
  $stmt = $pdo->prepare("
    SELECT branch_name, header_address, signature_path, signatory_name, signatory_position
    FROM branches
    WHERE upper(branch_code) = upper(:code)
    LIMIT 1
  ");
  $stmt->execute([':code' => $branch]);
  $br = $stmt->fetch(PDO::FETCH_ASSOC);

  if (is_array($br) && $br) {
    if (!empty($br['branch_name']))         $branch_name = $br['branch_name'];
    if (!empty($br['header_address']))      $branch_addr = $br['header_address'];
    if (!empty($br['signature_path']))      $branch_sig  = $br['signature_path'];
    if (!empty($br['signatory_name']))      $sign_name   = strtoupper($br['signatory_name']);
    if (!empty($br['signatory_position']))  $sign_pos    = $br['signatory_position'];
  }
} catch (Throwable $e) {
  // keep defaults
}

$flash_error = $_SESSION['flash_error'] ?? "";
unset($_SESSION['flash_error']);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Collection Notice Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
body{
  background:#ffffff;
  font-family:"Times New Roman", serif;
}
.card{ border:none; border-radius:16px; }
.card.shadow{ box-shadow:none !important; }

.topbar{
  background:#fff;
  border:1px solid rgba(0,0,0,.10);
  border-radius:14px;
  padding:10px 14px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:12px;
}
.topbar .title{ font-weight:bold; color:#111827; }
.topbar .meta{ font-size:12px; color:#6b7280; }

.user-btn{
  width:44px; height:44px;
  border-radius:999px;
  display:flex; align-items:center; justify-content:center;
  padding:0;
  border:none;
  background:linear-gradient(135deg, #5e72e4, #11cdef);
}
.user-btn i{ color:#fff; font-size:22px; }

.user-dd{
  border-radius:14px;
  border:1px solid rgba(0,0,0,.10);
  overflow:hidden;
}
.user-dd .dd-head{ background:#f6f8ff; padding:12px 14px; }
.user-dd .name{ font-weight:700; font-size:14px; color:#111827; line-height:1.1; }
.user-dd .meta{ font-size:12px; color:#6b7280; margin-top:3px; }
.user-dd .dropdown-item{ padding:10px 14px; }

.form-control, .custom-select{
  border-radius:12px;
  border:1px solid rgba(0,0,0,.10);
  box-shadow:none;
}
.form-control:focus, .custom-select:focus{
  border-color:rgba(94,114,228,.55);
  box-shadow:0 0 0 .2rem rgba(94,114,228,.15);
}
label{
  font-weight:700;
  font-size:12px;
  color:#374151;
  margin-bottom:6px;
}
.is-invalid{ border-color:#fb6340; background:#fff5f5; }

.btn-clear{
  background:#fff;
  border:1px solid rgba(0,0,0,.12);
  color:#111827;
  border-radius:14px;
  font-weight:800;
  padding:10px 14px;
}
.btn-print2{
  border:none;
  color:#fff;
  border-radius:14px;
  font-weight:900;
  padding:10px 14px;
  background:linear-gradient(135deg, #5e72e4, #8898ff);
}
.btn-pdf2{
  border:none;
  color:#fff;
  border-radius:14px;
  font-weight:900;
  padding:10px 14px;
  background:linear-gradient(135deg, #2dce89, #11cdef);
}

.search-wrap{ position:relative; }
.search-dd{
  position:absolute; top:100%; left:0; right:0;
  background:#fff; border:1px solid rgba(0,0,0,.12);
  border-radius:14px; z-index:9999; overflow:hidden; display:none;
}
.search-dd .item{ padding:10px 12px; cursor:pointer; border-bottom:1px solid rgba(0,0,0,.06); }
.search-dd .item:last-child{ border-bottom:none; }
.search-dd .item:hover, .search-dd .item.active{ background:#f3f6ff; }
.search-dd .muted{ color:#6b7280; font-size:12px; }
.hl{ background:#fff3cd; padding:0 2px; border-radius:3px; }

.bank-logo { height:52px; }
.bank-header { font-size:12px; line-height:1.2; }
.bank-header strong { font-size:12.5px; }
.preview-title{
  text-align:left;
  font-weight:700;
  margin-top:20px;
  margin-bottom:18px;
  font-size:18px;
}
.preview-date{
  margin-bottom:20px;
  text-align:right;
}
.preview-client-name{
  font-weight:700;
  text-transform:uppercase;
  margin-bottom:0;
}
.preview-line{ margin-bottom:0; }
.preview-body p{
  text-align:justify;
  margin-bottom:14px;
  text-indent:45px;
}
.sign-block{
  margin-top:24px;
  text-align:right;
}
.sig-img{
  max-height:80px;
  display:block;
  margin-left:auto;
  margin-bottom:4px;
}
.note-line{
  margin-top:32px;
  font-size:14px;
}
.receipt-block{
  margin-top:18px;
  font-size:14px;
}
.receipt-line{
  margin-top:8px;
}

:root{
  --pageMargin: 10mm;
}
#printArea{
  width:210mm;
  min-height:297mm;
  box-sizing:border-box;
  overflow:hidden;
  background:#ffffff;
  border:none;
  padding:var(--pageMargin) !important;
  margin-left:auto !important;
  margin-right:0 !important;
}
@media screen{
  #printArea{ border:1px solid #000 !important; }
}
#printArea #a4Content{
  width:100%;
  transform-origin:top left;
  transform:translate(var(--a4tx, 0px), var(--a4ty, 0px))
            scale(var(--a4sx, 1), var(--a4sy, 1));
  visibility:hidden;
}
#printArea.a4-ready #a4Content{ visibility:visible; }

@page { size:A4; margin:10mm; }
@media print {
  body { background:#fff !important; }
  .col-md-4, .topbar { display:none !important; }
  .col-md-8 { max-width:100% !important; flex:0 0 100% !important; }
  .card { border:none !important; box-shadow:none !important; }
  #printArea{
    width:auto !important;
    min-height:auto !important;
    padding:0 !important;
    overflow:visible !important;
    border:none !important;
    margin:0 !important;
  }
  #printArea #a4Content{ transform:none !important; visibility:visible !important; }
}
</style>
</head>

<body>
<div class="container-fluid mt-3">
  <div class="topbar">
    <div>
      <div class="title">Collection Notice Dashboard</div>
      <div class="meta">Branch: <strong><?= e($branch_name) ?></strong></div>
    </div>

    <div class="dropdown">
      <button class="user-btn" type="button" id="userMenu"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
              title="Account">
        <i class="bi bi-person-fill"></i>
      </button>

      <div class="dropdown-menu dropdown-menu-right user-dd" aria-labelledby="userMenu" style="min-width:240px;">
        <div class="dd-head">
          <div class="name"><?= e($user['full_name'] ?? 'User') ?></div>
          <div class="meta"><?= e($branch_name) ?></div>
        </div>

        <a class="dropdown-item text-danger" href="<?= e(url('logout')) ?>">
          <i class="bi bi-box-arrow-right mr-2"></i> Logout
        </a>
      </div>
    </div>
  </div>

  <?php if ($flash_error): ?>
    <div class="alert alert-danger"><?= e($flash_error) ?></div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <form action="<?= e(url('save_notice')) ?>" method="POST" id="noticeForm" novalidate>
            <input type="hidden" name="action_type" id="actionType" value="print">
            <input type="hidden" name="sign_name" id="signNameField" value="<?= e($sign_name) ?>">
            <input type="hidden" name="sign_position" id="signPositionField" value="<?= e($sign_pos) ?>">
            <input type="hidden" name="branch_name" id="branchNameField" value="<?= e($branch_name) ?>">
            <input type="hidden" name="branch_address" id="branchAddressField" value="<?= e(strip_tags(str_replace('<br>', ', ', $branch_addr))) ?>">
            <input type="hidden" name="notice_date" id="noticeDateInput" value="<?= date('Y-m-d') ?>">
            <input type="hidden" name="pn_no" id="pnNo">

            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="mb-0">Search Borrower</label>
              <div class="custom-control custom-switch" style="transform:scale(.9);">
                <input type="checkbox" class="custom-control-input" id="toggleAccountOnly">
                <label class="custom-control-label" for="toggleAccountOnly" style="font-size:12px;">Search by Account No only</label>
              </div>
            </div>

            <div class="search-wrap mb-2">
              <input class="form-control" id="borrowerSearch" placeholder="Type name or account no">
              <div class="search-dd" id="searchDropdown"></div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Account No.</label>
                <input class="form-control req" name="account_no" id="accountNo" oninput="syncAccountToPn(); updateAll();">
              </div>
              <div class="form-group col-md-4">
                <label>Loan Type</label>
                <select class="form-control req" name="loan_type" id="loanType" onchange="updateAll()">
                  <option value="">-- Select --</option>
                  <option value="Pangkabuhayan Loan">Pangkabuhayan Loan</option>
                  <option value="Agriculture Loan">Agriculture Loan</option>
                  <option value="Salary Loan">Salary Loan</option>
                  <option value="Personal Loan">Personal Loan</option>
                  <option value="Business Loan">Business Loan</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Principal Balance</label>
                <input class="form-control req" name="loan_amount" id="loanAmount" placeholder="0.00" oninput="updateAll()">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Notice No.</label>
                <select class="form-control req" name="notice_no" id="noticeNo" onchange="updateAll()">
                  <option value="">-- Select --</option>
                  <option value="FIRST">FIRST NOTICE</option>
                  <option value="SECOND">SECOND NOTICE</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>Past Due Since</label>
                <input type="date" class="form-control req" name="past_due_since" id="pastDueSince" onchange="updateAll()">
              </div>
            </div>

            <label>Salutation</label>
            <div id="salutationWrap" class="d-flex" style="gap:8px;flex-wrap:wrap;margin-bottom:10px;">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-value="Mr." onclick="setSalutation(this)">Mr.</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-value="Ms." onclick="setSalutation(this)">Ms.</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-value="Mrs." onclick="setSalutation(this)">Mrs.</button>
            </div>
            <input type="hidden" name="salutation" id="salutation">

            <div class="form-row">
              <div class="form-group col-md-4">
                <label>First Name</label>
                <input class="form-control req" name="first_name" id="firstName" oninput="updateAll()">
              </div>
              <div class="form-group col-md-4">
                <label>Middle Name</label>
                <input class="form-control" name="middle_name" id="middleName" oninput="updateAll()">
              </div>
              <div class="form-group col-md-4">
                <label>Last Name</label>
                <input class="form-control req" name="last_name" id="lastName" oninput="updateAll()">
              </div>
            </div>

            <div class="form-group">
              <label>Address</label>
              <input type="text" class="form-control req" name="address" id="address" oninput="updateAll()" placeholder="Complete address">
            </div>

            <div class="form-group">
              <label>Province</label>
              <input type="text" class="form-control req" name="province" id="province" oninput="updateAll()" placeholder="Province">
            </div>

            <div class="d-flex" style="gap:10px;">
              <button type="button" class="btn btn-clear flex-fill" id="btnClear">
                <i class="bi bi-arrow-counterclockwise mr-2"></i> Clear
              </button>

              <button type="button" class="btn btn-print2 flex-fill" id="btnPrintAction">
                <i class="bi bi-printer mr-2"></i> Print
              </button>

              <button type="button" class="btn btn-pdf2 flex-fill" id="btnPdfAction">
                <i class="bi bi-file-earmark-pdf mr-2"></i> Generate PDF
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card shadow mb-3">
        <div class="card-body notice-box" id="printArea">
          <div id="a4Content">
            <div class="d-flex justify-content-between align-items-start">
              <div class="text-left pr-1">
                <img src="<?= h(url('assets/img/bank_logo.jpg')) ?>" class="bank-logo" alt="Bank Logo">
              </div>
              <div class="text-right bank-header">
                <strong>AGRIBUSINESS BANKING CORPORATION - A RURAL BANK</strong><br>
                <strong id="p_branchName"><?= e($branch_name) ?></strong><br>
                <strong id="p_branchAddress"><?= $branch_addr ?></strong>
              </div>
            </div>
<hr>
            <div class="preview-title" id="p_noticeTitle"></div>

            <div class="preview-date" id="p_noticeDate"></div>

            <p class="preview-client-name" id="p_fullname"></p>
            <p class="preview-line" id="p_address"></p>
            <p class="preview-line" id="p_province"></p>

            <br>

            <p id="p_greeting"></p>

            <div class="preview-body" id="p_body"></div>
<br>
<br>
            <div class="sign-block">
              <p style="margin-bottom:22px;">Very truly yours,</p>
              <img id="p_signature" class="sig-img d-none" alt="Signature">
              <p style="font-weight:700; margin-bottom:0;" id="p_signName"><?= e($sign_name) ?></p>
              <p style="margin-top:0;" id="p_signPosition"><?= e($sign_pos) ?></p>
            </div>
<br>
<br>
            <div class="note-line">
              <strong>NOTE: Please disregard this notice if full payment has already been made.</strong>
            </div>

            <div class="receipt-block">
              <div class="receipt-line">Received by: ________________________________________________</div>
              <div class="receipt-line">Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_______________________________________________</div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

<script>
const APP_BASE = <?= json_encode($APP_BASE) ?>;
const BRANCH_SIGNATURE = <?= json_encode($branch_sig, JSON_UNESCAPED_SLASHES) ?>;

let ddItems = [];
let ddActiveIndex = -1;
let ddOpen = false;
let A4_READY = false;
let _fitRAF = null;

function clearStuckBackdrops(){
  document.querySelectorAll(".modal-backdrop, .dropdown-backdrop").forEach(b => b.remove());
  document.body.classList.remove("modal-open");
  document.body.style.removeProperty("padding-right");
}

function fitPreviewToA4(){
  if(!A4_READY) return;
  const area = document.getElementById("printArea");
  const content = document.getElementById("a4Content");
  if(!area || !content) return;

  if (_fitRAF) cancelAnimationFrame(_fitRAF);

  _fitRAF = requestAnimationFrame(()=>{
    const cs = getComputedStyle(area);

    const padL = parseFloat(cs.paddingLeft)  || 0;
    const padR = parseFloat(cs.paddingRight) || 0;
    const padT = parseFloat(cs.paddingTop)   || 0;
    const padB = parseFloat(cs.paddingBottom)|| 0;

    const availW = area.clientWidth  - padL - padR;
    const availH = area.clientHeight - padT - padB;

    const contW = content.scrollWidth;
    const contH = content.scrollHeight;
    if(contW <= 0 || contH <= 0) return;

    let sx = Math.min(1, (availW / contW)) * 0.99;
    let sy = Math.min(1, (availH / contH)) * 0.99;

    area.style.setProperty("--a4tx", `${padL}px`);
    area.style.setProperty("--a4ty", `${padT}px`);
    area.style.setProperty("--a4sx", sx);
    area.style.setProperty("--a4sy", sy);
  });
}
window.addEventListener("resize", ()=> { if(A4_READY) fitPreviewToA4(); });

async function waitForImagesIn(el){
  const imgs = Array.from(el.querySelectorAll("img"));
  if(!imgs.length) return;
  await Promise.all(imgs.map(img => new Promise(resolve => {
    if (img.complete) return resolve();
    img.addEventListener("load", resolve, { once:true });
    img.addEventListener("error", resolve, { once:true });
  })));
}
function nextFrame(){ return new Promise(r => requestAnimationFrame(()=>r())); }

function normalizeSpaces(s){ return String(s || "").replace(/\s+/g," ").trim(); }
function titleCaseNamePart(str){
  const s = normalizeSpaces(str).toLowerCase();
  if(!s) return "";
  return s.split(" ").map(w => w ? (w.charAt(0).toUpperCase() + w.slice(1)) : "").join(" ");
}
function formatPrettyDate(yyyyMmDd){
  if(!yyyyMmDd) return "";
  const d = new Date(yyyyMmDd);
  if(isNaN(d.getTime())) return yyyyMmDd;
  return d.toLocaleDateString('en-US', { month:'long', day:'2-digit', year:'numeric' });
}
function fmtMoney(n){
  const cleaned = String(n || '').replace(/[^0-9.\-]/g, '');
  const v = Number(cleaned) || 0;
  return v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function syncAccountToPn(){
  pnNo.value = accountNo.value || "";
}
function bodyTextByNotice(){
  const acct = normalizeSpaces(accountNo.value);
  const loanAmt = fmtMoney(loanAmount.value);
  const dueDate = formatPrettyDate(pastDueSince.value);
  const notice = normalizeSpaces(noticeNo.value).toUpperCase();

  if (notice === 'SECOND') {
    return `
      <p>This refers to your loan account with Agribusiness Banking Corporation - A Rural Bank covered by Promisory Note No. <strong>${acct}</strong> amounting to PHP <strong>${loanAmt}</strong>, excluding penalties and interests which has been left unpaid since <strong>${dueDate}</strong> despite our initial notice and follow-ups.</p>
      <br>
      <p>We are enjoining you for the <strong>second time,</strong> to please come and settle your obligation to the Bank to avoid further accumulation of interest and penalties that may arise on your account due to continued payment failure.</p>
      <br>
      <p>We will appreciate your prompt settlement of your obligation.</p>
    `;
  }

  return `
    <p>This refers to your loan account with Agribusiness Banking Corporation - A Rural Bank covered by Promisory Note No. <strong>${acct}</strong> <i>with an outstanding balance amounting</i> to PHP <strong>${loanAmt}</strong>, excluding <i>interests and penalties.</i></p>
    <br>
    <p>Please be reminded that your account has turned past due and has been left unpaid since <strong>${dueDate}</strong>.</p>
    <br>
    <p>We urge you to please come and settle your obligation to the Bank, to avoid further accumulation of interests and penalties that may arise due to failure of non-payment of the loan account.</p>
    <br>
    <p>We will appreciate your prompt settlement of your obligation.</p>
  `;
}

function setInvalid(el, on){
  if(!el) return;
  if(on) el.classList.add("is-invalid");
  else el.classList.remove("is-invalid");
}

function validateClient(showError=false){
  let ok = true;
  document.querySelectorAll('.req').forEach(i=>{
    if(!String(i.value || "").trim()){
      ok = false;
      if(showError) setInvalid(i, true);
    } else {
      setInvalid(i, false);
    }
  });

  const saluOk = !!(salutation.value || "").trim();
  if(!saluOk){
    ok=false;
    if(showError){
      salutationWrap.style.border = "1px solid #fb6340";
      salutationWrap.style.background = "#fff5f5";
    }
  } else {
    salutationWrap.style.border = "";
    salutationWrap.style.background = "#f8fafc";
  }

  return ok;
}

function setSalutation(btn){
  document.querySelectorAll('#salutationWrap .btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  salutation.value = btn.dataset.value || "";
  updateAll();
}

function updateAll(){
  syncAccountToPn();

  const fn = normalizeSpaces(firstName.value);
  const mn = normalizeSpaces(middleName.value);
  const ln = normalizeSpaces(lastName.value);

  const fullUpper = [fn, mn, ln].filter(Boolean).join(" ").trim().toUpperCase();
  p_fullname.innerText = fullUpper;
  p_address.innerText = normalizeSpaces(address.value);
  p_province.innerText = normalizeSpaces(province.value);

  const salu = normalizeSpaces(salutation.value);
  const l = titleCaseNamePart(ln);
  p_greeting.innerText = (salu && l) ? `Dear ${salu} ${l},` : "Dear ______,";

  const nTitle = normalizeSpaces(noticeNo.value).toUpperCase();
  p_noticeTitle.innerText = (nTitle ? `${nTitle} NOTICE` : "FIRST NOTICE");

  p_noticeDate.innerText = formatPrettyDate(noticeDateInput.value);
  p_body.innerHTML = bodyTextByNotice();

  p_signName.innerText = signNameField.value || "BRANCH MANAGER";
  p_signPosition.innerText = signPositionField.value || "BRANCH MANAGER";
  p_branchName.innerText = branchNameField.value || "BRANCH";
  p_branchAddress.innerHTML = (branchAddressField.value || "").replace(/\n/g, "<br>");

  const sig = document.getElementById("p_signature");
  if (BRANCH_SIGNATURE) {
    sig.src = APP_BASE + "/" + BRANCH_SIGNATURE.replace(/^\/+/, "");
    sig.classList.remove("d-none");
  } else {
    sig.classList.add("d-none");
    sig.removeAttribute("src");
  }

  fitPreviewToA4();
}

btnClear.addEventListener("click", ()=>{
  if(!confirm("Clear all form fields?")) return;

  borrowerSearch.value = "";
  accountNo.value = "";
  pnNo.value = "";
  loanType.value = "";
  noticeNo.value = "";
  noticeDateInput.value = "<?= date('Y-m-d') ?>";
  pastDueSince.value = "";
  loanAmount.value = "";
  salutation.value = "";
  firstName.value = "";
  middleName.value = "";
  lastName.value = "";
  address.value = "";
  province.value = "";
  document.querySelectorAll('#salutationWrap .btn').forEach(b=>b.classList.remove('active'));
  updateAll();
});

noticeForm.addEventListener("submit", (e)=>{
  if(!validateClient(true)){
    e.preventDefault();
    alert("Please complete all required fields.");
  }
});

document.getElementById("btnPrintAction").addEventListener("click", async ()=>{
  if(!validateClient(true)){
    alert("Please complete all required fields.");
    return;
  }

  updateAll();

  try{
    const fd = new FormData(document.getElementById("noticeForm"));
    fd.set("action_type", "save_only");
    const res = await fetch(APP_BASE + "/save_notice", { method:"POST", body: fd });
    const data = await res.json();
    if(!data.ok){
      alert(data.message || "Save failed.");
      return;
    }
  }catch(e){
    alert("Save failed (network).");
    return;
  }

  if (document.fonts && document.fonts.ready) {
    try { await document.fonts.ready; } catch(e){}
  }

  const sig = document.getElementById("p_signature");
  if (sig && sig.src) {
    await new Promise(resolve => {
      if (sig.complete) return resolve();
      sig.addEventListener("load", resolve, { once:true });
      sig.addEventListener("error", resolve, { once:true });
    });
  }

  window.print();
});

document.getElementById("btnPdfAction").addEventListener("click", async ()=>{
  if(!validateClient(true)){
    alert("Please complete all required fields.");
    return;
  }

  updateAll();

  try{
    const fd = new FormData(document.getElementById("noticeForm"));
    fd.set("action_type", "save_only");
    const res = await fetch(APP_BASE + "/save_notice", { method:"POST", body: fd });
    const data = await res.json();
    if(!data.ok){
      alert(data.message || "Save failed.");
      return;
    }
  }catch(e){
    alert("Save failed (network).");
    return;
  }

  if (document.fonts && document.fonts.ready) {
    try { await document.fonts.ready; } catch(e){}
  }

  const sig = document.getElementById("p_signature");
  if (sig && sig.src) {
    await new Promise(resolve => {
      if (sig.complete) return resolve();
      sig.addEventListener("load", resolve, { once:true });
      sig.addEventListener("error", resolve, { once:true });
    });
  }

  const area = document.getElementById("printArea");
  const prevBorder = area.style.border;
  area.style.border = "none";

  const canvas = await html2canvas(area, {
    scale: 3,
    useCORS: true,
    allowTaint: true,
    backgroundColor: "#ffffff",
    logging: false
  });

  area.style.border = prevBorder;

  const imgData = canvas.toDataURL("image/png", 1.0);
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF("p", "mm", "a4");
  pdf.addImage(imgData, "PNG", 0, 0, 210, 297);

  const acct = (document.getElementById("accountNo").value || "notice").replace(/[^a-zA-Z0-9_-]/g, "_");
  pdf.save(`notice_${acct}.pdf`);
});

function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, m => ({
    "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
  }[m]));
}
function highlight(text, q){
  if(!q) return escapeHtml(text);
  const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "ig");
  return escapeHtml(text).replace(re, m => `<span class="hl">${escapeHtml(m)}</span>`);
}
function openDropdown(){ searchDropdown.style.display="block"; ddOpen=true; }
function closeDropdown(){ searchDropdown.style.display="none"; ddItems=[]; ddActiveIndex=-1; ddOpen=false; }
function renderDropdown(q){
  if(ddItems.length === 0){
    searchDropdown.innerHTML = `<div class="item"><div><strong>No results found</strong></div><div class="muted">Try a different keyword.</div></div>`;
    openDropdown();
    return;
  }
  searchDropdown.innerHTML = ddItems.map((it, idx)=>{
    const title = `${it.full_name || ""}`.trim() || "(No Name)";
    const sub = `Acct: ${it.account_no || "-"} • Loan: ${it.loan_type || "-"}`;
    return `
      <div class="item ${idx===ddActiveIndex?'active':''}" data-idx="${idx}">
        <div><strong>${highlight(title, q)}</strong></div>
        <div class="muted">${highlight(sub, q)}</div>
      </div>
    `;
  }).join("");
  openDropdown();

  [...searchDropdown.querySelectorAll(".item")].forEach(el=>{
    el.addEventListener("mousedown", (ev)=>{
      ev.preventDefault();
      const idx = parseInt(el.getAttribute("data-idx"), 10);
      selectDropdownItem(idx);
    });
  });
}
function selectDropdownItem(idx){
  const it = ddItems[idx];
  if(!it) return;

  accountNo.value    = it.account_no || "";
  pnNo.value         = it.account_no || "";
  loanType.value     = it.loan_type || "";
  firstName.value    = it.first_name || "";
  middleName.value   = it.middle_name || "";
  lastName.value     = it.last_name || "";
  address.value      = it.address || "";
  province.value     = it.province || "";
  loanAmount.value   = it.loan_amount || "";
  pastDueSince.value = it.past_due_since || "";

  if(it.salutation){
    salutation.value = it.salutation;
    document.querySelectorAll('#salutationWrap .btn').forEach(b=>{
      b.classList.toggle('active', b.dataset.value === it.salutation);
    });
  }

  borrowerSearch.value = `${it.full_name} (${it.account_no})`;
  closeDropdown();
  updateAll();
}

let searchTimer = null;
async function doSearch(){
  const q = borrowerSearch.value.trim();
  if(q.length < 1){ closeDropdown(); return; }
  const accountOnly = toggleAccountOnly.checked ? 1 : 0;

  const res = await fetch(`${APP_BASE}/search_borrower?q=${encodeURIComponent(q)}&accountOnly=${accountOnly}`);
  const data = await res.json();

  if(Array.isArray(data)) ddItems = data;
  else ddItems = data.items || [];

  ddActiveIndex = ddItems.length ? 0 : -1;
  renderDropdown(q);
}
borrowerSearch.addEventListener("input", ()=>{
  clearTimeout(searchTimer);
  searchTimer = setTimeout(doSearch, 150);
});
borrowerSearch.addEventListener("focus", ()=>{
  if(borrowerSearch.value.trim().length >= 1) doSearch();
});
toggleAccountOnly.addEventListener("change", ()=>{
  if(borrowerSearch.value.trim().length >= 1) doSearch();
});
borrowerSearch.addEventListener("keydown", (e)=>{
  if(!ddOpen) return;

  if(e.key === "ArrowDown"){
    e.preventDefault();
    if(ddItems.length){
      ddActiveIndex = Math.min(ddItems.length-1, ddActiveIndex+1);
      renderDropdown(borrowerSearch.value.trim());
    }
  } else if(e.key === "ArrowUp"){
    e.preventDefault();
    if(ddItems.length){
      ddActiveIndex = Math.max(0, ddActiveIndex-1);
      renderDropdown(borrowerSearch.value.trim());
    }
  } else if(e.key === "Enter"){
    e.preventDefault();
    if(ddItems.length) selectDropdownItem(ddActiveIndex >= 0 ? ddActiveIndex : 0);
    else closeDropdown();
  } else if(e.key === "Escape"){
    closeDropdown();
  }
});
document.addEventListener("click", (e)=>{
  if(!e.target.closest(".search-wrap")) closeDropdown();
});

window.addEventListener("load", async ()=>{
  clearStuckBackdrops();
  setTimeout(clearStuckBackdrops, 0);
  setTimeout(clearStuckBackdrops, 150);

  const area = document.getElementById("printArea");
  if(area) area.classList.remove("a4-ready");

  updateAll();

  if (document.fonts && document.fonts.ready) {
    try { await document.fonts.ready; } catch(e){}
  }
  if(area) await waitForImagesIn(area);

  A4_READY = true;
  fitPreviewToA4();
  await nextFrame();

  if(area) area.classList.add("a4-ready");
});
</script>
</body>
</html>
