<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
  exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =============================
// 1) Honeypot (anti-bot)
// =============================
$website = trim(isset($_POST['website']) ? $_POST['website'] : '');
if ($website !== '') {
  // Bot preencheu o campo invisível
  http_response_code(200);
  echo json_encode(['ok' => true, 'message' => 'Mensagem enviada com sucesso!']);
  exit;
}

// =============================
// 2) Rate limit por IP
// =============================
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
$rateFile = __DIR__ . '/rate_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $ip) . '.txt';

$now = time();
$windowSeconds = 60;  // janela de 1 minuto
$maxRequests  = 3;    // no máximo 3 envios por minuto

$hits = [];
if (file_exists($rateFile)) {
  $content = trim(@file_get_contents($rateFile));
  if ($content !== '') $hits = explode(',', $content);
}

// mantém só os hits dentro da janela
$newHits = [];
foreach ($hits as $t) {
  if (ctype_digit($t) && ($now - (int)$t) < $windowSeconds) {
    $newHits[] = $t;
  }
}

if (count($newHits) >= $maxRequests) {
  http_response_code(429);
  echo json_encode(['ok' => false, 'message' => 'Muitas tentativas. Aguarde 1 minuto e tente novamente.']);
  exit;
}

$newHits[] = (string)$now;
@file_put_contents($rateFile, implode(',', $newHits));

// =============================
// 3) Sanitização + Validação forte
// =============================
$nome     = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
$email    = trim(isset($_POST['email']) ? $_POST['email'] : '');
$telefone = trim(isset($_POST['telefone']) ? $_POST['telefone'] : '');
$mensagem = trim(isset($_POST['mensagem']) ? $_POST['mensagem'] : '');

// limites de tamanho (impede lixo enorme)
if (strlen($nome) < 2 || strlen($nome) > 60) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Nome inválido.']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Email inválido.']);
  exit;
}

if (strlen($mensagem) < 10 || strlen($mensagem) > 1000) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Mensagem deve ter entre 10 e 1000 caracteres.']);
  exit;
}

// telefone: mantém só dígitos e valida tamanho BR (10 ou 11 normalmente)
$telDigits = preg_replace('/\D+/', '', $telefone);
if ($telDigits !== '' && (strlen($telDigits) < 10 || strlen($telDigits) > 11)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Telefone inválido. Use DDD + número.']);
  exit;
}

// heurística anti-gibberish (pega esses "XrBabXwH...")
function looks_like_gibberish($s) {
  $s = preg_replace('/\s+/', '', $s);
  if ($s === '') return true;

  // se quase não tem vogal, bem provável ser lixo
  $vowels = preg_match_all('/[aeiouáéíóúãõàêîôû]/iu', $s);
  $len = strlen($s);
  if ($len >= 12 && ($vowels / max(1,$len)) < 0.20) return true;

  // se tem longas sequências aleatórias (muito comum em bot)
  if (preg_match('/[A-Za-z0-9]{18,}/', $s)) return true;

  return false;
}

if (looks_like_gibberish($nome) || looks_like_gibberish($mensagem)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Conteúdo inválido.']);
  exit;
}

// =============================
// 4) Monta e envia o e-mail
// =============================
$destino = 'criart@janisonpublicidade.com.br';
$assunto = 'Novo contato pelo site - Janison Publicidade';

$bodyHtml = "
  <h2>Novo contato pelo site</h2>
  <p><strong>Nome:</strong> " . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . "</p>
  <p><strong>Email:</strong> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</p>
  <p><strong>Telefone:</strong> " . htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8') . "</p>
  <p><strong>Mensagem:</strong><br>" . nl2br(htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8')) . "</p>
";

try {
  $mail = new PHPMailer(true);

  $mail->isSMTP();
  $mail->Host       = 'mail.janisonpublicidade.com.br';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'criart@janisonpublicidade.com.br';
  $mail->Password   = 'janison2014###'; // <-- TROQUE e não exponha
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port       = 465;

  $mail->setFrom('criart@janisonpublicidade.com.br', 'Site Janison Publicidade');
  $mail->addReplyTo($email, $nome);
  $mail->addAddress($destino);

  $mail->isHTML(true);
  $mail->Subject = $assunto;
  $mail->Body    = $bodyHtml;

  $mail->send();

  echo json_encode(['ok' => true, 'message' => 'Mensagem enviada com sucesso!']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'message' => 'Erro ao enviar.']);
}
