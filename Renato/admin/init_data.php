<?php
require_once __DIR__ . '/database.php';

$db = new Database();

// 1. Configurações (Textos)
$config_data = [
    'whatsapp' => '5527999999999',
    'cor_primaria' => '#FF3B00',
    'layout' => 'editorial',
    'hero_kicker' => 'DESENVOLVIMENTO DE ALTA PERFORMANCE',
    'hero_titulo' => 'TRANSFORME',
    'hero_subtitulo' => 'SUA EQUIPE',
    'hero_deck' => 'Metodologia comprovada. Resultados mensuráveis.',
    'stat1_num' => '50+',
    'stat1_label' => 'Empresas',
    'stat2_num' => '15K',
    'stat2_label' => 'Pessoas',
    'stat3_num' => '8+',
    'stat3_label' => 'Anos',
    'video_texto' => 'DESCUBRA O MÉTODO',
    'meth_titulo' => 'Quem anda não entende quem voa',
    'meth_texto1' => '8 anos desenvolvendo metodologias únicas.',
    'meth_texto2' => 'Cada treinamento é customizado.',
    'cta_titulo' => 'Pronto para transformar seus resultados?',
    'cta_texto' => 'Agende uma conversa e descubra como podemos ajudar sua equipe.',
    'depoimentos_titulo' => 'RESULTADOS COMPROVADOS',
];

foreach ($config_data as $key => $value) {
    $db->setConfig($key, $value);
}

// 2. Mídias (Imagens/Vídeos Principais)
$db->setMedia('hero_image', 'assets/images/IMG_0753.webp', 'image/webp');
$db->setMedia('video_principal', 'assets/videos/video_de_vendas.mp4', 'video/mp4');

// 3. Depoimentos em Vídeo
$db->addDepoimentoVideo([
    'caminho' => 'uploads/depoimentos/depoimento_1765566887_693c69a744cc7.mp4',
    'tipo' => 'video/mp4',
    'nome_cliente' => 'Cliente Padrão',
    'cargo' => 'CEO',
    'empresa' => 'Empresa Teste',
]);

// 4. Depoimentos em Texto
$db->addDepoimentoTexto([
    'texto' => 'O trabalho do Renato transformou a cultura da nossa empresa. Resultados acima do esperado!',
    'nome_cliente' => 'Maria Silva',
    'cargo' => 'Diretora de RH',
    'empresa' => 'Tech Solutions',
    'localizacao' => 'São Paulo/SP',
]);

echo "Dados iniciais do CMS criados com sucesso.\n";
?>
