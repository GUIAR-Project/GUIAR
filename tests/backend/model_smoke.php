<?php

declare(strict_types=1);

define('BASE_URL', 'http://127.0.0.1:8080');

require_once __DIR__ . '/../../mvc/Models/Empresa.php';
require_once __DIR__ . '/../../mvc/Models/Administrador.php';
require_once __DIR__ . '/../../mvc/Models/Pedido.php';

use mvc\Models\Administrador;
use mvc\Models\Empresa;
use mvc\Models\Pedido;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Falha: {$message}\n");
        exit(1);
    }
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec(<<<'SQL'
CREATE TABLE empresa (
    id_empresa INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_empresa TEXT NOT NULL,
    CNPJ TEXT NOT NULL,
    nome_usuario TEXT NOT NULL,
    email TEXT NOT NULL,
    senha TEXT NOT NULL,
    nome_arquivo TEXT
);
CREATE TABLE administrador (
    id_adm INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_adm TEXT NOT NULL,
    nome_usuario TEXT NOT NULL,
    nome_foto TEXT,
    senha TEXT NOT NULL,
    FK_EMPRESA_id_empresa INTEGER NOT NULL
);
CREATE TABLE pedido (
    id_pedido INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_cliente TEXT NOT NULL,
    preco REAL NOT NULL,
    endereco TEXT NOT NULL,
    bairro TEXT NOT NULL,
    descricao TEXT,
    id_empresa INTEGER NOT NULL,
    id_adm INTEGER NOT NULL,
    latitude REAL,
    longitude REAL,
    status TEXT NOT NULL,
    telefone_cliente TEXT,
    token_rastreio TEXT,
    entregue_em TEXT,
    rota_ordem INTEGER,
    rota_trecho_s INTEGER,
    id_entregador INTEGER
);
SQL);

$empresa = new Empresa($pdo);
check($empresa->create([
    'nome_empresa' => 'Empresa CI',
    'cnpj' => '00.000.000/0001-00',
    'nome_usuario' => 'empresa_ci',
    'email' => 'empresa-ci@example.com',
    'senha' => 'senha-teste',
    'nome_arquivo' => 'logo.png',
]), 'Empresa::create não inseriu o registro.');

$empresaRecord = $empresa->getById(1);
check($empresaRecord['nome_usuario'] === 'empresa_ci', 'Empresa::getById não retornou o registro.');

$administrador = new Administrador($pdo);
check($administrador->create([
    'nome_adm' => 'Administrador CI',
    'nome_usuario' => 'admin_ci',
    'nome_foto' => 'foto.png',
    'senha' => 'senha-teste',
    'company_id' => 1,
]), 'Administrador::create não inseriu o registro.');
check(count($administrador->getByEmpresaId(1)) === 1, 'Administrador::getByEmpresaId não retornou o registro.');
check($administrador->isFirstAdminOfCompany(1, 1), 'O primeiro administrador da empresa não foi identificado.');
$adminLogado = $administrador->login('admin_ci', 'senha-teste');
check($adminLogado !== false && (int)$adminLogado['FK_EMPRESA_id_empresa'] === 1, 'Administrador::login não autenticou o administrador e sua empresa.');
check($administrador->login('admin_ci', 'senha-incorreta') === false, 'Administrador::login aceitou uma senha incorreta.');
check($administrador->create([
    'nome_adm' => 'Outro Administrador CI',
    'nome_usuario' => 'admin_ci_2',
    'nome_foto' => 'foto2.png',
    'senha' => 'senha-teste',
    'company_id' => 1,
]), 'O segundo administrador da empresa não foi cadastrado.');
check(!$administrador->isFirstAdminOfCompany(2, 1), 'Um administrador posterior foi identificado como o primeiro da empresa.');
check($administrador->isFirstAdminOfCompany(1, 1), 'O primeiro administrador deixou de ser identificado após novo cadastro.');
check($administrador->updateFirstAdminPassword(1, 'senha-nova'), 'A recuperação não atualizou o primeiro administrador.');
check($administrador->login('admin_ci', 'senha-nova') !== false, 'A nova senha do primeiro administrador não autentica.');
check($administrador->login('admin_ci_2', 'senha-teste') !== false, 'A recuperação alterou a senha de outro administrador.');
check(!$administrador->updateFirstAdminPassword(999, 'senha-nova'), 'A recuperação aceitou uma empresa sem administrador.');
$segundaEmpresaCriada = $empresa->create([
    'nome_empresa' => 'Segunda Empresa CI',
    'cnpj' => '11.111.111/0001-11',
    'nome_usuario' => 'empresa_ci_2',
    'email' => 'empresa-ci-2@example.com',
    'senha' => 'senha-teste',
    'nome_arquivo' => 'logo2.png',
]);
check($segundaEmpresaCriada, 'A segunda empresa de teste não foi cadastrada.');
check($administrador->create([
    'nome_adm' => 'Administrador da Segunda Empresa',
    'nome_usuario' => 'admin_ci_3',
    'nome_foto' => 'foto3.png',
    'senha' => 'senha-teste',
    'company_id' => 2,
]), 'O administrador da segunda empresa não foi cadastrado.');
check($administrador->isFirstAdminOfCompany(3, 2), 'A primeira conta administrativa de outra empresa não foi identificada.');
check(!$administrador->isFirstAdminOfCompany(2, 2), 'Um administrador de outra empresa recebeu permissão indevida.');

$pedido = new Pedido($pdo);
check($pedido->create([
    'nome_cliente' => 'Cliente CI',
    'preco' => 25.50,
    'endereco' => 'Rua de teste, 1',
    'bairro' => 'Centro',
    'descricao' => 'Pedido de teste',
    'id_empresa' => 1,
    'id_adm' => 1,
    'latitude' => -22.591,
    'longitude' => -46.523,
]), 'Pedido::create não inseriu o registro.');
check($pedido->getById(1)['nome_cliente'] === 'Cliente CI', 'Pedido::getById não retornou o registro.');
check(preg_match('/^[a-f0-9]{32}$/', (string)$pedido->getById(1)['token_rastreio']) === 1, 'Pedido::create não gerou o token de rastreio.');
check($pedido->getByTokenRastreio('nao-e-token') === false, 'Pedido::getByTokenRastreio aceitou token inválido.');
check(\mvc\Models\Pedido::normalizarTelefone('(19) 99343-3297') === '19993433297', 'Pedido::normalizarTelefone não limpou o número.');

fwrite(STDOUT, "Smoke tests dos Models concluídos.\n");
