<?php
// dashboard.php
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/app.php";
require_login();

$user = $_SESSION['user'] ?? [];
$APP_BASE = base_url();

$branch = strtoupper(trim($user['branch_code'] ?? 'CAUAYAN'));
if (strpos($branch, 'SANTIAGO') !== false) $branch = 'SANTIAGO';
if (strpos($branch, 'CAUAYAN') !== false)  $branch = 'CAUAYAN';

if ($branch === 'SANTIAGO') {
  $branch_name = "SANTIAGO BRANCH";
  $branch_addr = "NATIONAL ROAD, PLARIDEL,<br>SANTIAGO CITY, ISABELA";
  $branch_sig  = "assets/signatures/santiago.png";
  $sign_name   = "PAULINO VEA CAUILAN";
  $sign_pos    = "BRANCH LOANS HEAD";
} else {
  $branch_name = "CAUAYAN BRANCH";
  $branch_addr = "LCU BLDG. NATIONAL HIGHWAY, CABARUAN, DISTRICT 2,<br>CAUAYAN CITY, ISABELA 3305";
  $branch_sig  = "assets/signatures/cauayan.png";
  $sign_name   = "JOSE ESMUNDO GALAPON";
  $sign_pos    = "BRANCH LOANS HEAD";
}

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
body { background:#ffffff; font-family:"Times New Roman", serif; }
.card { border:none; border-radius:16px; }
.card.shadow { box-shadow:none !important; }
.table th,.table td { border:1px solid #000 !important; padding:5px; vertical-align:middle; }
.locked { opacity:.4; pointer-events:none; }
.fade-slide { animation: slideFade .25s ease-in-out; }
@keyframes slideFade { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
.is-invalid { border-color:#fb6340; background:#fff5f5; }
.btn-group .btn.active { background:#5e72e4; color:#fff; }
.ro-input { background:#f6f8ff; }

.topbar{
  background:#fff; border:1px solid rgba(0,0,0,.10); border-radius:14px;
  padding:10px 14px; display:flex; justify-content:space-between; align-items:center;
  box-shadow:none; margin-bottom:12px;
}
.topbar .title{ font-weight:bold; color:#111827; }
.topbar .meta{ font-size:12px; color:#6b7280; }

.user-btn{
  width:44px; height:44px;
  border-radius:999px;
  display:flex; align-items:center; justify-content:center;
  padding:0;
  border:none;
  background: linear-gradient(135deg, #5e72e4, #11cdef);
}
.user-btn i{ color:#fff; font-size:22px; }
.user-dd{
  border-radius:14px;
  border:1px solid rgba(0,0,0,.10);
  box-shadow:none;
  overflow:hidden;
}
.user-dd .dd-head{ background:#f6f8ff; padding:12px 14px; }
.user-dd .name{ font-weight:700; font-size:14px; color:#111827; line-height:1.1; }
.user-dd .meta{ font-size:12px; color:#6b7280; margin-top:3px; }
.user-dd .dropdown-item{ padding:10px 14px; }

.form-control, .custom-select {
  border-radius:12px;
  border:1px solid rgba(0,0,0,.10);
  box-shadow:none;
}
.form-control:focus, .custom-select:focus{
  border-color: rgba(94,114,228,.55);
  box-shadow: 0 0 0 .2rem rgba(94,114,228,.15);
}
label { font-weight:700; font-size:12px; color:#374151; margin-bottom:6px; }
textarea.form-control { min-height: 90px; }
#salutationWrap{
  background:#f8fafc;
  border:1px dashed rgba(0,0,0,.10);
  padding:8px;
  border-radius:12px;
}
#salutationWrap .btn{
  border-radius:999px;
  padding:6px 12px;
  font-weight:700;
}
#salutationWrap .btn.active{
  background: linear-gradient(135deg, #5e72e4, #11cdef);
  border-color: transparent;
  color:#fff;
}
#inputTable thead th{ background:#f6f8ff; font-size:12px; }
#inputTable .form-control-sm{ border-radius:10px; }

.btn-clear{
  background:#fff;
  border:1px solid rgba(0,0,0,.12);
  color:#111827;
  border-radius:14px;
  font-weight:800;
  padding:10px 14px;
}
.btn-next2{
  border:none;
  color:#fff;
  border-radius:14px;
  font-weight:900;
  padding:10px 14px;
  background: linear-gradient(135deg, #5e72e4, #11cdef);
}
.btn-print2{
  border:none;
  color:#fff;
  border-radius:14px;
  font-weight:900;
  padding:10px 14px;
  background: linear-gradient(135deg, #5e72e4, #8898ff);
}
.btn-pdf2{
  border:none;
  color:#fff;
  border-radius:14px;
  font-weight:900;
  padding:10px 14px;
  background: linear-gradient(135deg, #2dce89, #11cdef);
}

.search-wrap{ position:relative; }
.search-dd{
  position:absolute; top:100%; left:0; right:0;
  background:#fff; border:1px solid rgba(0,0,0,.12);
  border-radius:14px; box-shadow:none;
  z-index:9999; overflow:hidden; display:none;
}
.search-dd .item{ padding:10px 12px; cursor:pointer; border-bottom:1px solid rgba(0,0,0,.06); }
.search-dd .item:last-child{ border-bottom:none; }
.search-dd .item:hover, .search-dd .item.active{ background:#f3f6ff; }
.search-dd .muted{ color:#6b7280; font-size:12px; }
.hl{ background:#fff3cd; padding:0 2px; border-radius:3px; }

.toast-wrap{ position:fixed; top:18px; right:18px; z-index:99999; }

.bank-logo { height:52px; }
.bank-header { font-size:12px; line-height:1.15; }
.bank-header strong { font-size:12.5px; }
.hr-strong{ border:0; border-top:3px solid #000; margin:10px 0; }
.sig-img { max-height:80px; display:block; margin-top:8px; }
.sig-name { margin-top:6px; font-weight:bold; text-transform:uppercase; }
.sig-pos { margin-top:-2px; }
.notice-label { text-align:center; margin:0; font-weight:bold; }
.disregard { text-align:center; margin:0; }
.ack-title { text-align:center; margin:0; }

.ack-wrap{
  margin-top:8px;
  margin-left:0 !important;
  margin-right:0 !important;
  width:520px;
  max-width:100%;
  margin: 8px auto 0 auto !important;
}
.ack-small { font-size:14px; }
.ack-row{ display:flex; align-items:flex-end; gap:10px; margin:8px 0; }
.ack-label{ width:210px; white-space:nowrap; }
.ack-colon{ width:12px; text-align:center; }
.ack-fill{ flex:1; border-bottom:1px solid #000; height:18px; }
.ack-fill.relation-line{ height:16px; border-bottom-width:2px; }

:root{
  --pageMargin: 8mm;
}
#printArea{
  width: 210mm;
  height: 297mm;
  box-sizing: border-box;
  overflow: hidden;
  background: #ffffff;
  border:none;
  padding: var(--pageMargin) !important;
  margin-left: auto !important;
  margin-right: 0 !important;
}
@media screen{
  #printArea{ border: 1px solid #000 !important; }
}
#printArea #a4Content{
  width: 100%;
  transform-origin: top left;
  transform: translate(var(--a4tx, 0px), var(--a4ty, 0px))
             scale(var(--a4sx, 1), var(--a4sy, 1));
  visibility: hidden;
}
#printArea.a4-ready #a4Content{ visibility: visible; }

@page { size: A4; margin: 8mm; }
@media print {
  body { background:#fff !important; }
  .col-md-4, .topbar, .toast-wrap { display:none !important; }
  .col-md-8 { max-width:100% !important; flex:0 0 100% !important; }
  .card { border:none !important; box-shadow:none !important; }

  #printArea{
    width:auto !important;
    height:auto !important;
    padding:0 !important;
    overflow:visible !important;
    border:none !important;
    margin:0 !important;
  }
  #printArea #a4Content{ transform:none !important; visibility: visible !important; }
}
</style>
</head>

<body>
<div class="toast-wrap" id="toastWrap"></div>

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

        <a class="dropdown-item text-danger" href="<?= e(url('logout.php')) ?>">
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
        <div class="btn-group btn-group-toggle d-flex">
          <button type="button" id="btnClient" class="btn btn-outline-primary active w-50" onclick="showSection('client')">Client Details</button>
          <button type="button" id="btnAmort" class="btn btn-outline-primary w-50" onclick="showSection('amort')">Amortization Details</button>
        </div>

        <div class="card-body">
          <form action="<?= e(url('save_notice.php')) ?>" method="POST" id="noticeForm" novalidate>
            <input type="hidden" name="action_type" id="actionType" value="print">

            <div id="sectionClient" class="fade-slide">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="mb-0">Search Borrower</label>
                <div class="custom-control custom-switch" style="transform:scale(.9);">
                  <input type="checkbox" class="custom-control-input" id="toggleAccountOnly">
                  <label class="custom-control-label" for="toggleAccountOnly" style="font-size:12px;">Search by Account No only</label>
                </div>
              </div>

              <div class="search-wrap">
                <input class="form-control mb-2" id="borrowerSearch" placeholder="Type name or account no">
                <div class="search-dd" id="searchDropdown"></div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label>Account No.</label>
                  <input class="form-control req" name="account_no" id="accountNo" oninput="updateAll()">
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
                  <label>Notice No.</label>
                  <select class="form-control req" name="notice_no" id="noticeNo" onchange="updateAll()">
                    <option value="">-- Select --</option>
                    <option value="FIRST">First</option>
                    <option value="SECOND">Second</option>
                    <option value="THIRD">Third</option>
                  </select>
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

              <label>Address</label>
              <input type="text" class="form-control req mb-2" name="address" id="address" oninput="updateAll()" placeholder="Complete address">

              <div class="d-flex" style="gap:10px;">
                <button type="button" class="btn btn-clear flex-fill" id="btnClear">
                  <i class="bi bi-arrow-counterclockwise mr-2"></i> Clear
                </button>

                <button type="button" class="btn btn-next2 flex-fill" onclick="goNextClient()">
                  Next <i class="bi bi-arrow-right ml-2"></i>
                </button>
              </div>

              <input type="hidden" name="borrower" id="borrowerFull">
              <input type="hidden" name="sign_name" value="<?= e($sign_name) ?>">
              <input type="hidden" name="sign_position" value="<?= e($sign_pos) ?>">
            </div>

            <div id="sectionAmort" class="d-none locked fade-slide">
              <table class="table table-sm mt-2" id="inputTable">
                <thead>
                  <tr>
                    <th>Due Date</th>
                    <th>Amo</th>
                    <th>Penalty</th>
                    <th>Total</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>

              <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">➕ Add Row</button>
              <hr>

              <div class="d-flex" style="gap:10px;">
                <button type="button" class="btn btn-print2 flex-fill" id="btnPrintAction">
                  <i class="bi bi-printer mr-2"></i> Print
                </button>

                <button type="button" class="btn btn-pdf2 flex-fill" id="btnPdfAction">
                  <i class="bi bi-file-earmark-pdf mr-2"></i> Generate PDF
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card shadow mb-3">
        <div class="card-body notice-box" id="printArea">

          <div class="d-flex justify-content-between align-items-start">
            <div class="text-left pr-1">
              <img src="<?= h(url('assets/img/bank_logo.jpg')) ?>" class="bank-logo" alt="Bank Logo">
            </div>
            <div class="text-right bank-header">
              <strong>AGRIBUSINESS BANKING CORPORATION – A RURAL BANK</strong><br>
              <strong><?= e($branch_name) ?></strong><br>
              <strong><?= $branch_addr ?></strong>
            </div>
          </div>

          <hr class="hr-strong">

          <div class="text-left mt-3" id="noticeDate"></div>
          <br>

          <p class="font-weight-bold mb-1 text-uppercase" id="p_fullname"></p>
          <p id="p_address"></p>
          <br>

          <p class="mb-1" id="p_greeting"></p>
          <br>

          <p>
            This refers to your <strong><span id="p_loanType"></span></strong> with
            <strong>Agribusiness Banking Corporation - <?= e($branch_name) ?></strong> covered by Promissory Note No.
            <strong><u><span id="p_accountNo"></span></u></strong>.
          </p>

          <p>
            As per record, it shows that your <strong><span id="p_period"></span></strong> amortization remains unpaid as of this date
            <strong><span id="p_date_inline"></span></strong>, and it already accumulated to
            <strong><span id="p_words"></span></strong> (₱<span id="p_total"></span>) including penalties. See computation below;
          </p>

          <table class="table table-sm">
            <thead>
              <tr>
                <th>Due Date</th>
                <th>Amortization</th>
                <th>Interest</th>
                <th>Penalty</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody id="previewTable"></tbody>
            <tfoot>
              <tr class="font-weight-bold">
                <td>TOTAL</td>
                <td id="sumAmoRight"></td>
                <td id="sumIntRight"></td>
                <td id="sumPenRight"></td>
                <td id="grandTotal"></td>
              </tr>
            </tfoot>
          </table>

          <p>
            We urge you to please come and settle your obligation to the Bank seven days (7) upon receipt of this letter
            to avoid further accumulation of interest and penalties that may arise including filing of legal case due to your failure
            to pay on the required due date.
          </p>
          <p>We will appreciate your prompt settlement of your obligation.</p>

          <br>
          <p>Very truly yours,</p>

          <div class="sig-wrap">
            <img id="p_signature" class="sig-img d-none" alt="Digital Signature">

            <div class="sig-name" id="p_signName"><?= e($sign_name) ?></div>
            <div class="sig-pos" id="p_signPosition"><?= e($sign_pos) ?></div>
            <br><br>

            <div id="p_noticeLabel" class="notice-label"></div>
            <div class="disregard"><strong>Please disregard this notice if payment has been made.</strong></div>
            <hr class="hr-strong">
            <div class="ack-title"><strong>ACKNOWLEDGEMENT</strong></div>

            <div class="ack-wrap ack-small">
              <p class="mb-2">This is to certify that I received the Notice:</p>

              <div class="ack-row">
                <div class="ack-label">For the account of</div><div class="ack-colon">:</div><div class="ack-fill"></div>
              </div>
              <div class="ack-row">
                <div class="ack-label">Amount Due</div><div class="ack-colon">:</div><div class="ack-fill"></div>
              </div>
              <div class="ack-row">
                <div class="ack-label">Date Received</div><div class="ack-colon">:</div><div class="ack-fill"></div>
              </div>
              <div class="ack-row">
                <div class="ack-label">Received By</div><div class="ack-colon">:</div><div class="ack-fill"></div>
              </div>
              <div class="ack-row">
                <div class="ack-label">Signature</div><div class="ack-colon">:</div><div class="ack-fill"></div>
              </div>

              <p class="mt-3 mb-1 text-center">If the receiver is not the borrower, how is he/she related to him/her?</p>
              <div class="ack-row" style="margin-top:4px;">
                <div class="ack-label" style="width:0; min-width:0;"></div>
                <div class="ack-colon" style="visibility:hidden;">:</div>
                <div class="ack-fill relation-line"></div>
              </div>
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
const INTEREST_PERCENT_PER_ROW = 5;
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

function ensureA4Wrapper(){
  const area = document.getElementById("printArea");
  if(!area) return;
  if(area.querySelector("#a4Content")) return;

  const wrap = document.createElement("div");
  wrap.id = "a4Content";

  while(area.firstChild){
    wrap.appendChild(area.firstChild);
  }
  area.appendChild(wrap);
}

function fitPreviewToA4(){
  if(!A4_READY) return;

  ensureA4Wrapper();

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

    let sx = (availW / contW);
    let sy = (availH / contH);

    sx = Math.min(1, sx) * 0.98;
    sy = Math.min(1, sy) * 0.94;

    const NUDGE_LEFT_PX = 14;
    const NUDGE_TOP_PX  = 6;

    area.style.setProperty("--a4tx", `${Math.max(0, padL - NUDGE_LEFT_PX)}px`);
    area.style.setProperty("--a4ty", `${Math.max(0, padT - NUDGE_TOP_PX)}px`);
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
function toTitleCase(str){ return String(str || "").toLowerCase().replace(/\b([a-z])/g, m => m.toUpperCase()); }
function titleCaseNamePart(str){
  const s = normalizeSpaces(str).toLowerCase();
  if(!s) return "";
  return s.split(" ").map(w => w ? (w.charAt(0).toUpperCase() + w.slice(1)) : "").join(" ");
}
function fmtMoney(n){
  const v = Number(n) || 0;
  return v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function formatPrettyDate(yyyyMmDd){
  if(!yyyyMmDd) return "";
  const d = new Date(yyyyMmDd);
  if(isNaN(d.getTime())) return yyyyMmDd;
  return d.toLocaleDateString('en-US', { month:'long', day:'2-digit', year:'numeric' });
}
function formatMonthYear(d){ return d.toLocaleDateString('en-US', { month:'long', year:'numeric' }); }

function numberToWords(n){
  const o=["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine"];
  if(n===0) return "Zero";
  if(n<10)return o[n];
  if(n<20)return["Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"][n-10];
  if(n<100)return["","","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"][Math.floor(n/10)]+" "+o[n%10];
  if(n<1000)return o[Math.floor(n/100)]+" Hundred "+numberToWords(n%100);
  return numberToWords(Math.floor(n/1000))+" Thousand "+numberToWords(n%1000);
}
function amountToWordsWithFraction(amount){
  const safe = Math.max(0, Number(amount) || 0);
  const pesos = Math.floor(safe + 1e-9);
  const cents = Math.round((safe - pesos) * 100);
  const pesoWords = numberToWords(pesos) + " Pesos";
  if(cents > 0) return `${pesoWords} and ${String(cents).padStart(2,'0')}/100 Only`;
  return `${pesoWords} Only`;
}

function showSection(type){
  if(type==='client'){
    sectionClient.classList.remove('d-none');
    sectionAmort.classList.add('d-none');
    btnClient.classList.add('active');
    btnAmort.classList.remove('active');
  }
  if(type==='amort'){
    if(!validateClient(true)){
      alert("Please complete Client Details first.");
      return;
    }
    sectionClient.classList.add('d-none');
    sectionAmort.classList.remove('d-none','locked');
    btnClient.classList.remove('active');
    btnAmort.classList.add('active');

    if(inputTable.tBodies[0].rows.length === 0){
      addRow();
    }
  }
}
function goNextClient(){
  if(!validateClient(true)){
    alert("Please complete required Client Details first.");
    return;
  }
  showSection('amort');
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
      ok=false;
      if(showError) setInvalid(i,true);
    } else setInvalid(i,false);
  });

  const saluOk = !!(salutation.value || "").trim();
  if(!saluOk){
    ok=false;
    if(showError) {
      salutationWrap.style.border = "1px solid #fb6340";
      salutationWrap.style.background = "#fff5f5";
    }
  } else {
    salutationWrap.style.border = "";
    salutationWrap.style.background = "#f8fafc";
  }

  sectionAmort.classList.toggle('locked', !ok);
  return ok;
}
function validateAmortRows(){
  const rows = [...inputTable.tBodies[0].rows];
  if(rows.length === 0) return false;
  for(const r of rows){
    const dd = r.querySelector('input[name="due_date[]"]')?.value || "";
    if(!dd.trim()) return false;
  }
  return true;
}

function setSalutation(btn){
  document.querySelectorAll('#salutationWrap .btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  salutation.value = btn.dataset.value || "";
  updateAll();
}

function computePeriodFromDueDates(){
  const dueDates = [];
  [...inputTable.tBodies[0].rows].forEach(r=>{
    const v = r.querySelector('input[name="due_date[]"]')?.value;
    if(!v) return;
    const d = new Date(v);
    if(!isNaN(d.getTime())) dueDates.push(d);
  });
  if(dueDates.length === 0) return "";
  dueDates.sort((a,b)=>a-b);

  const uniq = [];
  const seen = new Set();
  for(const d of dueDates){
    const key = d.getFullYear() + "-" + String(d.getMonth()+1).padStart(2,'0');
    if(!seen.has(key)){
      seen.add(key);
      uniq.push(new Date(d.getFullYear(), d.getMonth(), 1));
    }
  }
  if(uniq.length === 1) return formatMonthYear(uniq[0]);
  if(uniq.length === 2) return `${formatMonthYear(uniq[0])} and ${formatMonthYear(uniq[1])}`;
  return `${formatMonthYear(uniq[0])} to ${formatMonthYear(uniq[uniq.length-1])}`;
}

function sortInputRowsByDueDate(){
  const tbody = inputTable.tBodies[0];
  const rows = Array.from(tbody.rows);
  rows.sort((ra, rb) => {
    const va = ra.querySelector('input[name="due_date[]"]')?.value || "";
    const vb = rb.querySelector('input[name="due_date[]"]')?.value || "";
    if(!va && !vb) return 0;
    if(!va) return 1;
    if(!vb) return -1;
    return new Date(va) - new Date(vb);
  });
  rows.forEach(r => tbody.appendChild(r));
}

function addRow(){
  const r = inputTable.tBodies[0].insertRow();
  r.innerHTML = `
    <td><input type="date" class="form-control form-control-sm" name="due_date[]" onchange="updateAll()"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm" name="amort[]" onchange="updateAll()"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm" name="penalty[]" onchange="updateAll()" placeholder="0.00"></td>
    <td><input type="text" class="form-control form-control-sm ro-input text-center" value="0.00" readonly tabindex="-1"></td>
    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove();updateAll()">✖</button></td>
  `;
  updateAll();
}

function getNoticeLabel(){
  const v = normalizeSpaces(noticeNo.value);
  if(v === "FIRST")  return "THIS IS OUR FIRST LETTER (FIRST NOTICE)";
  if(v === "SECOND") return "THIS IS OUR SECOND LETTER (SECOND NOTICE)";
  if(v === "THIRD")  return "THIS IS OUR THIRD LETTER (THIRD NOTICE)";
  return "";
}

function updateAll(){
  sortInputRowsByDueDate();

  const sig = document.getElementById("p_signature");
  if(BRANCH_SIGNATURE){
    sig.src = APP_BASE + "/" + BRANCH_SIGNATURE.replace(/^\/+/, "");
    sig.classList.remove("d-none");
  } else {
    sig.classList.add("d-none");
    sig.removeAttribute("src");
  }

  const fn = normalizeSpaces(firstName.value);
  const mn = normalizeSpaces(middleName.value);
  const ln = normalizeSpaces(lastName.value);
  const full = [fn,mn,ln].filter(Boolean).join(" ").replace(/\s+/g," ").trim();
  borrowerFull.value = full;

  p_fullname.innerText = full;
  p_address.innerText = toTitleCase(address.value);
  p_loanType.innerText = loanType.value;
  p_accountNo.innerText = accountNo.value;

  const now = new Date();
  const dateText = now.toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
  noticeDate.innerText = dateText;
  p_date_inline.innerText = dateText;

  const salu = normalizeSpaces(salutation.value);
  const l = titleCaseNamePart(ln);
  const fullTC = [titleCaseNamePart(fn), titleCaseNamePart(mn), titleCaseNamePart(ln)].filter(Boolean).join(" ").trim();
  if(salu && l) p_greeting.innerText = `Dear ${salu} ${l},`;
  else if(salu && fullTC) p_greeting.innerText = `Dear ${salu} ${fullTC},`;
  else p_greeting.innerText = "Dear ,";

  p_noticeLabel.innerText = getNoticeLabel();

  const periodText = computePeriodFromDueDates();
  p_period.innerText = periodText || "________________";

  let sumAmo = 0, sumPen = 0, sumTot = 0;
  let interestPercentTotal = 0;
  previewTable.innerHTML = "";

  const rows = [...inputTable.tBodies[0].rows];
  rows.forEach(r=>{
    const due = r.querySelector('input[name="due_date[]"]')?.value || "";
    const amort = parseFloat(r.querySelector('input[name="amort[]"]')?.value || "0") || 0;
    const pen = parseFloat(r.querySelector('input[name="penalty[]"]')?.value || "0") || 0;
    const rowTotal = amort + pen;

    sumAmo += amort;
    sumPen += pen;
    sumTot += rowTotal;
    interestPercentTotal += INTEREST_PERCENT_PER_ROW;

    const leftTotal = r.cells[3].querySelector("input");
    if(leftTotal) leftTotal.value = fmtMoney(rowTotal);

    previewTable.innerHTML += `
      <tr>
        <td>${formatPrettyDate(due)}</td>
        <td>${fmtMoney(amort)}</td>
        <td>5%</td>
        <td>${fmtMoney(pen)}</td>
        <td>${fmtMoney(rowTotal)}</td>
      </tr>
    `;
  });

  sumAmoRight.innerText = fmtMoney(sumAmo);
  sumPenRight.innerText = fmtMoney(sumPen);
  sumIntRight.innerText = (interestPercentTotal ? interestPercentTotal.toFixed(0) : 0) + "%";
  p_total.innerText = fmtMoney(sumTot);
  grandTotal.innerText = fmtMoney(sumTot);
  p_words.innerText = amountToWordsWithFraction(sumTot);

  fitPreviewToA4();
}

btnClear.addEventListener("click", ()=>{
  if(!confirm("Clear all form fields and rows?")) return;

  borrowerSearch.value = "";
  accountNo.value = "";
  loanType.value = "";
  noticeNo.value = "";
  salutation.value = "";
  firstName.value = "";
  middleName.value = "";
  lastName.value = "";
  address.value = "";
  borrowerFull.value = "";
  document.querySelectorAll('#salutationWrap .btn').forEach(b=>b.classList.remove('active'));
  inputTable.tBodies[0].innerHTML = "";
  updateAll();
});

noticeForm.addEventListener("submit", (e)=>{
  if(!validateClient(true)){
    e.preventDefault();
    alert("Please complete required Client Details first.");
    showSection("client");
    return;
  }
  if(!validateAmortRows()){
    e.preventDefault();
    alert("Please fill the Due Date for all amortization rows.");
    return;
  }
});

document.getElementById("btnPrintAction").addEventListener("click", async ()=>{
  if(!validateClient(true)){
    alert("Please complete required Client Details first.");
    showSection("client");
    return;
  }
  if(!validateAmortRows()){
    alert("Please fill the Due Date for all amortization rows.");
    return;
  }

  updateAll();

  try{
    const fd = new FormData(document.getElementById("noticeForm"));
    fd.set("action_type", "save_only");
    const res = await fetch(APP_BASE + "/save_notice.php", { method:"POST", body: fd });
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
    alert("Please complete required Client Details first.");
    showSection("client");
    return;
  }
  if(!validateAmortRows()){
    alert("Please fill the Due Date for all amortization rows.");
    return;
  }

  updateAll();

  try{
    const fd = new FormData(document.getElementById("noticeForm"));
    fd.set("action_type", "save_only");
    const res = await fetch(APP_BASE + "/save_notice.php", { method:"POST", body: fd });
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
  await new Promise(r => setTimeout(r, 240));

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

  accountNo.value = it.account_no || "";
  loanType.value  = it.loan_type || "";
  firstName.value = it.first_name || "";
  middleName.value= it.middle_name || "";
  lastName.value  = it.last_name || "";
  address.value   = it.address || "";

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

  const res = await fetch(`${APP_BASE}/search_borrower.php?q=${encodeURIComponent(q)}&accountOnly=${accountOnly}`);
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

  A4_READY = false;
  ensureA4Wrapper();
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
