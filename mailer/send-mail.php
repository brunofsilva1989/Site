<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Se quiser restringir para seu domínio (ajuda contra abuse):
// header('Access-Control-Allow-Origin: https://SEU-DOMINIO.com.br');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
  exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ====== Pegando e validando dados ======
$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');

if ($nome === '' || $email === '' || $mensagem === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Preencha Nome, Email e Mensagem.']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Email inválido.']);
  exit;
}

// ====== Configurações do email ======
$destino = 'criart@janisonpublicidade.com.br'; // PARA onde você quer receber
$assunto = 'Novo contato pelo site - Janison Publicidade';

$bodyHtml = "
  <h2>Novo contato pelo site</h2>
  <p><strong>Nome:</strong> " . htmlspecialchars($nome) . "</p>
  <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
  <p><strong>Telefone:</strong> " . htmlspecialchars($telefone) . "</p>
  <p><strong>Mensagem:</strong><br>" . nl2br(htmlspecialchars($mensagem)) . "</p>
";

try {
  $mail = new PHPMailer(true);

  // ===== SMTP (recomendado) =====
  // Use os dados SMTP do seu e-mail na Hostinger (ou do provedor que você usa).
  $mail->isSMTP();
  $mail->Host       = 'smtp.janisonpublicidade.com.br';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'criart@janisonpublicidade.com.br';
  $mail->Password   = 'janison2014###';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // ou PHPMailer::ENCRYPTION_SMTPS
  $mail->Port       = 587; // 465 (SMTPS) ou 587 (STARTTLS)

  // Remetente
  $mail->setFrom('criart@janisonpublicidade.com.br', 'Site Janison Publicidade');

  // Responder para o email da pessoa
  $mail->addReplyTo($email, $nome);

  // Destinatário
  $mail->addAddress($destino);

  $mail->isHTML(true);
  $mail->Subject = $assunto;
  $mail->Body    = $bodyHtml;
  $mail->AltBody = "Nome: $nome\nEmail: $email\nTelefone: $telefone\nMensagem:\n$mensagem";

  $mail->send();

  echo json_encode(['ok' => true, 'message' => 'Mensagem enviada com sucesso!']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'message' => 'Erro ao enviar. Verifique SMTP/senha.',
    'debug' => $e->getMessage()
  ]);
}
