<?php
require_once __DIR__ . '/admin/database.php';

$db = new Database();

// Carregar configurações
$whatsapp = $db->getConfig('whatsapp', '5527999999999');
$cor = $db->getConfig('cor_primaria', '#FF3B00');
$hero_kicker = $db->getConfig('hero_kicker', 'DESENVOLVIMENTO DE ALTA PERFORMANCE');
$hero_titulo = $db->getConfig('hero_titulo', 'TRANSFORME');
$hero_subtitulo = $db->getConfig('hero_subtitulo', 'SUA EQUIPE');
$hero_deck = $db->getConfig('hero_deck', 'Metodologia comprovada. Resultados mensuráveis.');
$stat1_num = $db->getConfig('stat1_num', '50+');
$stat1_label = $db->getConfig('stat1_label', 'Empresas');
$stat2_num = $db->getConfig('stat2_num', '15K');
$stat2_label = $db->getConfig('stat2_label', 'Pessoas');
$stat3_num = $db->getConfig('stat3_num', '8+');
$stat3_label = $db->getConfig('stat3_label', 'Anos');
$video_texto = $db->getConfig('video_texto', 'DESCUBRA O MÉTODO');
$meth_titulo = $db->getConfig('meth_titulo', 'Quem anda não entende quem voa');
$meth_texto1 = $db->getConfig('meth_texto1', '8 anos desenvolvendo metodologias únicas.');
$meth_texto2 = $db->getConfig('meth_texto2', 'Cada treinamento é customizado.');
$cta_titulo = $db->getConfig('cta_titulo', 'Pronto para transformar seus resultados?');
$cta_texto = $db->getConfig('cta_texto', 'Agende uma conversa e descubra como podemos ajudar sua equipe.');
$depoimentos_titulo = $db->getConfig('depoimentos_titulo', 'RESULTADOS COMPROVADOS');

// Carregar mídias
$heroImage = $db->getImage('hero_image');
$videoPrincipal = $db->getVideo('video_principal');

// Carregar depoimentos
$depoimentosVideo = $db->getDepoimentosVideo(true);
$depoimentosTexto = $db->getDepoimentosTexto(true);

$whatsapp_link = "https://wa.me/" . $whatsapp;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renato Araújo — Metodologia de Alta Performance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --accent: <?= $cor ?>;
            --dark: #0D0D0D;
            --darker: #000000;
            --light: #FFFFFF;
            --gray: #6B6B6B;
            --border: #2A2A2A;
        }

        body {
            font-family: 'IBM Plex Sans', sans-serif;
            background: var(--dark);
            color: var(--light);
            line-height: 1.4;
            overflow-x: hidden;
        }

        /* Hero Magazine Style */
        .hero {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            background: var(--darker);
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            opacity: 0.3;
        }

        .hero-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(100%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            padding: 0 60px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 100px;
            align-items: center;
        }

        .hero-text {
            border-left: 4px solid var(--accent);
            padding-left: 40px;
        }

        .hero-kicker {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 18px;
            letter-spacing: 4px;
            color: var(--accent);
            margin-bottom: 20px;
        }

        .hero h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 140px;
            line-height: 0.9;
            letter-spacing: -2px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .hero h1 span {
            display: block;
            color: var(--accent);
        }

        .hero-deck {
            font-size: 24px;
            line-height: 1.5;
            color: var(--gray);
            margin-bottom: 50px;
            font-weight: 300;
        }

        .hero-stats {
            display: flex;
            gap: 60px;
        }

        .stat {
            border-top: 2px solid var(--accent);
            padding-top: 15px;
        }

        .stat-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 64px;
            line-height: 1;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
        }

        .cta-hero {
            position: absolute;
            bottom: 60px;
            right: 60px;
            background: var(--accent);
            color: var(--light);
            padding: 24px 60px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            clip-path: polygon(0 0, 100% 0, 95% 100%, 0% 100%);
        }

        .cta-hero:hover {
            clip-path: polygon(5% 0, 100% 0, 100% 100%, 0% 100%);
            padding: 24px 80px;
        }

        /* Video Feature */
        .video-feature {
            padding: 140px 60px;
            background: var(--darker);
            position: relative;
        }

        .video-feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 1px;
            height: 100px;
            background: linear-gradient(to bottom, transparent, var(--accent));
        }

        .video-wrapper-main {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            aspect-ratio: 16/9;
            background: #000;
            cursor: pointer;
            overflow: hidden;
        }

        .video-wrapper-main video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-overlay-main {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 59, 0, 0.3), rgba(0, 0, 0, 0.8));
            backdrop-filter: blur(20px) saturate(150%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
        }

        .video-overlay-main.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .video-overlay-main:hover {
            backdrop-filter: blur(15px) saturate(180%);
        }

        .play-button-main {
            width: 120px;
            height: 120px;
            border: 3px solid var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 59, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .video-overlay-main:hover .play-button-main {
            transform: scale(1.1);
            background: rgba(255, 59, 0, 0.2);
        }

        .play-button-main::after {
            content: '';
            width: 0;
            height: 0;
            border-left: 30px solid var(--accent);
            border-top: 20px solid transparent;
            border-bottom: 20px solid transparent;
            margin-left: 6px;
        }

        .video-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 32px;
            letter-spacing: 3px;
            text-align: center;
        }

        /* Methodology Section */
        .methodology {
            padding: 140px 60px;
            background: var(--dark);
        }

        .meth-grid {
            max-width: 1600px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
        }

        .meth-content h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 96px;
            line-height: 0.9;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .meth-content p {
            font-size: 20px;
            line-height: 1.7;
            color: var(--gray);
            margin-bottom: 30px;
        }

        .meth-visual {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .meth-img {
            aspect-ratio: 1;
            overflow: hidden;
            position: relative;
        }

        .meth-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(100%);
            transition: all 0.5s ease;
        }

        .meth-img:hover img {
            filter: grayscale(0%);
            transform: scale(1.05);
        }

        .meth-img:first-child {
            grid-column: span 2;
        }

        /* Video Carousel */
        .video-carousel-container {
            max-width: 1600px;
            margin: 0 auto 80px;
            position: relative;
        }

        .video-carousel-track {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(300px, 1fr); /* Pelo menos 300px, mas pode expandir */
            gap: 24px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-padding: 24px;
            padding-bottom: 10px; /* Espaço para a barra de rolagem */
            -webkit-overflow-scrolling: touch;
        }

        .video-carousel-item {
            scroll-snap-align: start;
            padding: 0 10px; /* Ajuste para espaçamento */
            box-sizing: border-box;
        }

        /* Remover botões de navegação se o scroll for o principal */
        .carousel-prev, .carousel-next {
            display: none;
        }

        /* Pequeno ajuste para mobile */
        @media (max-width: 768px) {
            .video-carousel-track {
                grid-auto-columns: minmax(280px, 1fr);
            }
        }

        /* New Text and Video Section Styles */
        .text-video-feature {
            padding: 100px 60px;
            background: var(--dark);
            text-align: center;
        }

        .container-text-video {
            max-width: 900px;
            margin: 0 auto;
        }

        .text-block-intro {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 28px;
            letter-spacing: 2px;
            color: var(--accent);
            margin-bottom: 20px;
        }

        .text-block-body {
            font-size: 22px;
            line-height: 1.6;
            color: var(--light);
            margin-bottom: 20px;
            font-weight: 300;
        }

        .text-block-outro {
            font-size: 18px;
            line-height: 1.5;
            color: var(--gray);
            margin-bottom: 50px;
        }

        .video-wrapper-new {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            aspect-ratio: 16/9;
            background: #000;
            cursor: pointer;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
        }

        .video-wrapper-new video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-overlay-new {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 59, 0, 0.4), rgba(0, 0, 0, 0.7));
            backdrop-filter: blur(10px) saturate(180%);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
        }

        .video-overlay-new.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .video-overlay-new:hover {
            backdrop-filter: blur(15px) saturate(200%);
        }

        .play-button-new {
            width: 90px;
            height: 90px;
            border: 3px solid var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 59, 0, 0.2);
            transition: all 0.3s ease;
        }

        .video-overlay-new:hover .play-button-new {
            transform: scale(1.1);
            background: rgba(255, 59, 0, 0.3);
        }

        .play-button-new svg {
            fill: var(--light);
            width: 40px;
            height: 40px;
            margin-left: 8px; /* Ajuste para o triângulo */
        }

        /* Media Queries for New Section */
        @media (max-width: 1024px) {
            .text-video-feature {
                padding: 80px 30px;
            }
            .text-block-intro {
                font-size: 24px;
            }
            .text-block-body {
                font-size: 18px;
            }
            .text-block-outro {
                font-size: 16px;
            }
            .play-button-new {
                width: 70px;
                height: 70px;
            }
            .play-button-new svg {
                width: 30px;
                height: 30px;
            }
        }

        @media (max-width: 768px) {
            .text-video-feature {
                padding: 60px 20px;
            }
            .text-block-intro {
                font-size: 20px;
            }
            .text-block-body {
                font-size: 16px;
            }
            .text-block-outro {
                font-size: 14px;
            }
            .play-button-new {
                width: 60px;
                height: 60px;
            }
            .play-button-new svg {
                width: 25px;
                height: 25px;
            }
        }

        @media (max-width: 480px) {
            .text-video-feature {
                padding: 40px 15px;
            }
            .text-block-intro {
                font-size: 18px;
            }
            .text-block-body {
                font-size: 14px;
            }
            .text-block-outro {
                font-size: 12px;
                margin-bottom: 30px;
            }
            .play-button-new {
                width: 50px;
                height: 50px;
            }
            .play-button-new svg {
                width: 20px;
                height: 20px;
            }
        }

        .video-depoimento {
            position: relative;
            cursor: pointer;
            overflow: hidden;
            border: 2px solid var(--border);
            aspect-ratio: 16/9;
        }

        .video-depoimento video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.5);
            transition: filter 0.3s ease;
        }

        .video-depoimento:hover video {
            filter: brightness(0.7);
        }

        .video-play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 59, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: transform 0.3s ease;
        }

        .video-depoimento:hover .video-play-btn {
            transform: translate(-50%, -50%) scale(1.1);
        }

        .video-play-btn svg {
            width: 30px;
            height: 30px;
            fill: var(--light);
            margin-left: 5px;
        }

        .video-depoimento-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            z-index: 5;
        }

        .video-depoimento-info h4 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--accent);
        }

        .video-depoimento-info p {
            font-size: 13px;
            color: var(--gray);
        }

        .carousel-prev, .carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--accent);
            color: var(--light);
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 24px;
            z-index: 20;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .carousel-prev:hover, .carousel-next:hover {
            opacity: 1;
        }

        .carousel-prev {
            left: 0;
        }

        .carousel-next {
            right: 0;
        }

        @media (max-width: 1200px) {
            .video-carousel-item {
                min-width: 50%; /* 2 items per view */
            }
        }

        @media (max-width: 768px) {
            .video-carousel-item {
                min-width: 100%; /* 1 item per view */
            }
        }

        /* Video Modal */
        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .video-modal.active {
            display: flex;
        }

        .video-modal-content {
            max-width: 90%;
            width: 900px;
            position: relative;
        }

        .video-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: var(--light);
            font-size: 32px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 10px;
        }

        .video-modal video {
            width: 100%;
            max-height: 80vh;
        }

        /* Testimonials Editorial */
        .testimonials {
            padding: 140px 60px;
            background: var(--darker);
        }

        .test-header {
            text-align: center;
            margin-bottom: 100px;
        }

        .test-header h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 96px;
            line-height: 1;
            text-transform: uppercase;
        }

        .test-grid {
            max-width: 1600px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2px;
            background: var(--accent);
            border: 2px solid var(--accent);
        }

        .test-card {
            background: var(--darker);
            padding: 60px;
            position: relative;
        }

        .test-card::before {
            content: '"';
            position: absolute;
            top: 30px;
            left: 30px;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 120px;
            color: var(--accent);
            opacity: 0.3;
            line-height: 1;
        }

        .test-quote {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .test-author {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .test-name {
            font-weight: 700;
            font-size: 16px;
            color: var(--accent);
        }

        .test-role {
            font-size: 14px;
            color: var(--gray);
        }

        .test-location {
            font-size: 12px;
            color: var(--gray);
            opacity: 0.6;
        }

        /* CTA Final */
        .cta-final {
            padding: 200px 60px;
            background: var(--dark);
            position: relative;
            text-align: center;
            overflow: hidden;
        }

        .cta-final::before {
            content: 'TRANSFORME';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Bebas Neue', sans-serif;
            font-size: 300px;
            color: var(--light);
            opacity: 0.05;
            line-height: 1;
            pointer-events: none;
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 80px;
            line-height: 1;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .cta-btn {
            background: var(--accent);
            color: var(--light);
            padding: 24px 60px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .cta-btn:hover {
            background: var(--light);
            color: var(--darker);
        }

        /* Footer */
        footer {
            padding: 30px 60px;
            background: var(--darker);
            text-align: center;
            border-top: 1px solid var(--border);
        }

        footer p {
            font-size: 12px;
            color: var(--gray);
        }

        /* Whatsapp Button */
        .whatsapp {
            position: fixed;
            bottom: 40px;
            right: 40px;
            width: 70px;
            height: 70px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .whatsapp:hover {
            transform: scale(1.1);
        }

        .whatsapp svg {
            width: 35px;
            height: 35px;
            fill: var(--light);
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 60px;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 24px;
            letter-spacing: 2px;
            color: var(--light);
        }

        .nav-cta {
            background: var(--accent);
            color: var(--light);
            padding: 12px 30px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-cta:hover {
            background: var(--light);
            color: var(--darker);
        }

        @media (max-width: 1200px) {
            .hero h1 { font-size: 100px; }
            .hero-content { grid-template-columns: 1fr; gap: 60px; }
            .meth-grid, .test-grid { grid-template-columns: 1fr; }
            .cta-final::before { font-size: 200px; }
        }

        @media (max-width: 768px) {
            .hero {
                height: auto;
                min-height: 100vh;
                padding: 100px 0 60px;
            }
            .hero h1 { 
                font-size: 60px !important; 
            }
            .hero-text {
                padding-left: 20px;
                border-left-width: 2px;
            }
            .hero-kicker {
                font-size: 14px;
            }
            .hero-deck {
                font-size: 18px;
            }
            .hero-stats { 
                flex-direction: column !important; 
                gap: 30px !important; 
            }
            .hero-content, .video-feature, .methodology, .testimonials, .cta-final {
                padding: 80px 30px !important;
            }
            nav { 
                padding: 20px 30px !important; 
            }
            .logo {
                font-size: 20px;
            }
            .nav-cta {
                padding: 10px 20px;
                font-size: 11px;
            }
            .test-card { 
                padding: 40px 30px !important; 
            }
            .test-card::before {
                font-size: 80px;
            }
            .test-header h2 {
                font-size: 56px;
            }
            .test-quote {
                font-size: 16px;
            }
            .cta-content h2 { 
                font-size: 60px !important; 
            }
            .cta-btn {
                padding: 20px 40px;
                font-size: 14px;
            }
            .stat-num { 
                font-size: 48px !important; 
            }
            .stat-label {
                font-size: 11px;
            }
            .cta-hero {
                position: relative !important;
                bottom: auto !important;
                right: auto !important;
                margin-top: 40px;
                display: inline-block;
                clip-path: none !important;
                padding: 20px 40px !important;
            }
            .meth-content h2 {
                font-size: 56px;
            }
            .meth-content p {
                font-size: 16px;
            }
            .meth-visual {
                grid-template-columns: 1fr !important;
            }
            .meth-img:first-child {
                grid-column: 1 !important;
            }
            .video-text {
                font-size: 24px;
            }
            .play-button-main {
                width: 80px;
                height: 80px;
            }
            .play-button-main::after {
                border-left: 20px solid var(--accent);
                border-top: 15px solid transparent;
                border-bottom: 15px solid transparent;
            }
            .whatsapp {
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
            }
            .whatsapp svg {
                width: 30px;
                height: 30px;
            }
        }

        @media (max-width: 480px) {
            .hero h1 { 
                font-size: 48px !important; 
            }
            .hero-kicker {
                font-size: 12px;
                letter-spacing: 2px;
            }
            .hero-deck {
                font-size: 16px;
            }
            .hero-content {
                padding: 0 20px !important;
            }
            .stat-num {
                font-size: 40px !important;
            }
            .stat-label {
                font-size: 10px;
            }
            .cta-content h2 {
                font-size: 48px !important;
            }
            .cta-final::before {
                font-size: 120px;
            }
            .meth-content h2 {
                font-size: 42px;
            }
            .test-header h2 {
                font-size: 42px;
            }
            .test-card {
                padding: 30px 20px !important;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">RENATO ARAÚJO</div>
        <a href="<?= $whatsapp_link ?>" class="nav-cta">Fale Comigo</a>
    </nav>

    <section class="hero">
        <div class="hero-bg">
            <img src="<?= htmlspecialchars($heroImage['caminho'] ?? 'assets/images/IMG_0753.webp') ?>" alt="Imagem Principal">
        </div>
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-kicker"><?= htmlspecialchars($hero_kicker) ?></div>
                <h1>
                    <?= htmlspecialchars($hero_titulo) ?>
                    <span><?= htmlspecialchars($hero_subtitulo) ?></span>
                </h1>
                <p class="hero-deck">
                    <?= nl2br(htmlspecialchars($hero_deck)) ?>
                </p>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-num"><?= htmlspecialchars($stat1_num) ?></span>
                    <span class="stat-label"><?= htmlspecialchars($stat1_label) ?></span>
                </div>
                <div class="stat">
                    <span class="stat-num"><?= htmlspecialchars($stat2_num) ?></span>
                    <span class="stat-label"><?= htmlspecialchars($stat2_label) ?></span>
                </div>
                <div class="stat">
                    <span class="stat-num"><?= htmlspecialchars($stat3_num) ?></span>
                    <span class="stat-label"><?= htmlspecialchars($stat3_label) ?></span>
                </div>
            </div>
        </div>
        <a href="<?= $whatsapp_link ?>" class="cta-hero">Começar Agora</a>
    </section>

    <!-- Video Feature -->
<section class="video-feature">
        <div class="video-wrapper-new" onclick="playMainVideo()">
            <video id="mainPlayer" preload="metadata">
                <source src="uploads/videos/69404308a8fe2_1765819144.mp4" type="video/mp4">
                Seu navegador não suporta vídeos HTML5.
            </video>
            <div class="video-overlay-new" id="mainOverlay">
                <div class="play-button-new">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 5V19L19 12L8 5Z"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <section class="text-video-feature">
        <div class="container-text-video">
            <p class="text-block-intro">
                Para você que deseja construir um time de vendas vencedor, que vai além das vendas, é criar relacionamentos e resultados.
            </p>
            <p class="text-block-body">
                Vender é conectar valores antes de fechar negócios.
            </p>
            <p class="text-block-outro">
                Quando o cliente percebe valor, o preço deixa de ser obstáculo.
            </p>
            <div class="video-wrapper-new" onclick="playSecondaryVideo()">
                <video id="secondaryPlayer" preload="metadata">
                    <source src="uploads/videos/vidin.MP4" type="video/mp4">
                    Seu navegador não suporta vídeos HTML5.
                </video>
                <div class="video-overlay-new" id="secondaryOverlay">
                    <div class="play-button-new">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 5V19L19 12L8 5Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function playMainVideo() {
            const video = document.getElementById('mainPlayer');
            const overlay = document.getElementById('mainOverlay');
            video.play();
            video.controls = true;
            overlay.classList.add('hidden');
        }

        function playSecondaryVideo() {
            const video = document.getElementById('secondaryPlayer');
            const overlay = document.getElementById('secondaryOverlay');
            video.play();
            video.controls = true;
            overlay.classList.add('hidden');
        }
    </script>

    <section class="methodology">
        <div class="meth-grid">
            <div class="meth-content">
                <h2><?= htmlspecialchars($meth_titulo) ?></h2>
                <p>
                    <?= nl2br(htmlspecialchars($meth_texto1)) ?>
                </p>
                <p>
                    <?= nl2br(htmlspecialchars($meth_texto2)) ?>
                </p>
            </div>
            <div class="meth-visual">
                <div class="meth-img">
                    <img src="assets/images/IMG_0754.webp" alt="">
                </div>
                <div class="meth-img">
                    <img src="assets/images/IMG_0756.webp" alt="">
                </div>
                <div class="meth-img">
                    <img src="assets/images/IMG_0757.webp" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="test-header">
            <h2><?= htmlspecialchars($depoimentos_titulo) ?></h2>
        </div>
        
        <!-- CARROSSEL DE VÍDEOS -->
        <?php if(!empty($depoimentosVideo)): ?>
        <div class="video-carousel-container">
            <div class="video-carousel-track" id="videoCarouselTrack">
                <?php foreach($depoimentosVideo as $dep): ?>
                <div class="video-carousel-item">
                    <div class="video-depoimento" data-video="/revexa_sistemas/Sistemas/RenatoCustom/<?= htmlspecialchars($dep['caminho']) ?>">
                        <video muted loop>
                            <source src="/revexa_sistemas/Sistemas/RenatoCustom/<?= htmlspecialchars($dep['caminho']) ?>" type="<?= htmlspecialchars($dep['tipo']) ?>">
                        </video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                        <div class="video-depoimento-info">
                            <?php if(!empty($dep['nome_cliente']) && $dep['nome_cliente'] != 'Cliente'): ?>
                            <h4><?= htmlspecialchars($dep['nome_cliente']) ?></h4>
                            <?php endif; ?>
                            <?php if($dep['cargo'] || $dep['empresa']): ?>
                            <p>
                                <?= htmlspecialchars($dep['cargo']) ?>
                                <?= $dep['cargo'] && $dep['empresa'] ? ' — ' : '' ?>
                                <?= htmlspecialchars($dep['empresa']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Botões de navegação removidos, pois o scroll-snap assume o controle -->
        </div>
        <?php endif; ?>
        
        <div class="test-grid" id="depoimentosTextoGrid">
            <?php if(!empty($depoimentosTexto)): ?>
                <?php foreach($depoimentosTexto as $dep): ?>
                <div class="test-card">
                    <p class="test-quote">
                        <?= nl2br(htmlspecialchars($dep['texto'])) ?>
                    </p>
                    <div class="test-author">
                        <span class="test-name"><?= htmlspecialchars($dep['nome_cliente']) ?></span>
                        <span class="test-role">
                            <?= htmlspecialchars($dep['cargo']) ?>
                            <?= $dep['cargo'] && $dep['empresa'] ? ' — ' : '' ?>
                            <?= htmlspecialchars($dep['empresa']) ?>
                        </span>
                        <?php if($dep['localizacao']): ?>
                        <span class="test-location"><?= htmlspecialchars($dep['localizacao']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Conteúdo estático de fallback se não houver depoimentos no CMS -->
                <div class="test-card">
                    <p class="test-quote">
                        Contratamos o Renato para treinar nossa equipe de vendas. 
                        Em 90 dias aumentamos 47% em conversão. O método dele é direto, 
                        prático e funciona de verdade. Melhor investimento do ano.
                    </p>
                    <div class="test-author">
                        <span class="test-name">Marcelo Ferreira</span>
                        <span class="test-role">Diretor Comercial — Metalúrgica Progresso</span>
                        <span class="test-location">Vitória, ES</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="cta-final" id="contato">
        <div class="cta-content">
            <h2><?= htmlspecialchars($cta_titulo) ?></h2>
            <a href="<?= $whatsapp_link ?>" class="cta-btn" target="_blank">Falar com Renato</a>
        </div>
    </section>

    <footer>
        <p>© 2024 RENATO ARAÚJO — TODOS OS DIREITOS RESERVADOS</p>
    </footer>

    <a href="<?= $whatsapp_link ?>" class="whatsapp" target="_blank">
        <svg viewBox="0 0 32 32">
            <path d="M16 0c-8.837 0-16 7.163-16 16 0 2.825 0.737 5.607 2.137 8.048l-2.137 7.952 7.933-2.127c2.42 1.37 5.173 2.127 8.067 2.127 8.837 0 16-7.163 16-16s-7.163-16-16-16zM16 29.467c-2.482 0-4.908-0.646-7.07-1.87l-0.507-0.292-4.713 1.262 1.262-4.669-0.292-0.508c-1.207-2.100-1.847-4.507-1.847-6.924 0-7.435 6.049-13.483 13.483-13.483s13.483 6.049 13.483 13.483-6.049 13.483-13.483 13.483zM21.694 18.42c-0.372-0.186-2.197-1.083-2.537-1.207-0.341-0.124-0.589-0.186-0.837 0.186s-0.961 1.207-1.178 1.455c-0.217 0.248-0.434 0.279-0.806 0.093s-1.573-0.579-2.995-1.849c-1.107-0.988-1.854-2.209-2.071-2.581s-0.023-0.573 0.163-0.759c0.167-0.166 0.372-0.434 0.558-0.651s0.248-0.372 0.372-0.62c0.124-0.248 0.062-0.465-0.031-0.651s-0.837-2.016-1.147-2.759c-0.31-0.744-0.62-0.651-0.837-0.651-0.217 0-0.465-0.031-0.713-0.031s-0.651 0.093-0.992 0.465c-0.341 0.372-1.302 1.27-1.302 3.101s1.333 3.597 1.519 3.845c0.186 0.248 2.581 3.938 6.251 5.525 0.875 0.372 1.557 0.589 2.088 0.744 0.744 0.279 1.457 0.248 2.005 0.155 0.713-0.093 2.197-0.899 2.507-1.766s0.31-1.612 0.217-1.766c-0.093-0.155-0.341-0.248-0.713-0.434z"/>
        </svg>
    </a>

    <!-- MODAL DE VÍDEO -->
    <div class="video-modal" id="videoModal">
        <div class="video-modal-content">
            <button class="video-modal-close" onclick="closeVideoModal()">×</button>
            <video id="modalVideo" controls></video>
        </div>
    </div>

    <script>
        // Lógica do Vídeo Principal
        const vid = document.getElementById('mainVid');
        const wrap = document.getElementById('videoWrap');
        const overlay = document.getElementById('vidOverlay');

        if (wrap) {
            wrap.addEventListener('click', () => {
                if (vid.paused) {
                    vid.play();
                    vid.controls = true;
                    overlay.classList.add('hidden');
                }
            });

            vid.addEventListener('ended', () => {
                overlay.classList.remove('hidden');
                vid.controls = false;
            });
        }

        // Lógica do Carrossel de Vídeos
        const track = document.getElementById('videoCarouselTrack');
        const prevBtn = document.getElementById('carouselPrev');
        const nextBtn = document.getElementById('carouselNext');
        const videoItems = document.querySelectorAll('.video-carousel-item');
        let currentIndex = 0;

        if (track && prevBtn && nextBtn && videoItems.length > 0) {
            const itemWidth = videoItems[0].offsetWidth;
            const itemsPerView = window.innerWidth > 1200 ? 3 : (window.innerWidth > 768 ? 2 : 1);
            const totalItems = videoItems.length;
            const maxIndex = totalItems - itemsPerView;

            const updateCarousel = () => {
                const offset = -currentIndex * itemWidth;
                track.style.transform = `translateX(${offset}px)`;
                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex >= maxIndex;
            };

            nextBtn.addEventListener('click', () => {
                if (currentIndex < maxIndex) {
                    currentIndex++;
                    updateCarousel();
                }
            });

            prevBtn.addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateCarousel();
                }
            });

            window.addEventListener('resize', () => {
                // Recalcula o índice e atualiza a visualização ao redimensionar
                currentIndex = 0;
                updateCarousel();
            });

            updateCarousel(); // Inicializa o carrossel
        }

        // Lógica do Modal de Vídeo
        const modal = document.getElementById('videoModal');
        const modalVideo = document.getElementById('modalVideo');
        const depoimentoVideos = document.querySelectorAll('.video-depoimento');

        depoimentoVideos.forEach(item => {
            item.addEventListener('click', () => {
                const videoPath = item.getAttribute('data-video');
                modalVideo.src = videoPath;
                modal.classList.add('active');
                modalVideo.play();
            });
        });

        function closeVideoModal() {
            modalVideo.pause();
            modalVideo.currentTime = 0;
            modal.classList.remove('active');
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeVideoModal();
            }
        });

        // Lógica para o novo vídeo
        const videoNew = document.getElementById('videoNew');
        const videoOverlayNew = document.querySelector('.video-overlay-new');
        const playButtonNew = document.querySelector('.play-button-new');

        if (videoOverlayNew) {
            videoOverlayNew.addEventListener('click', () => {
                if (videoNew.paused) {
                    videoNew.play();
                    videoOverlayNew.classList.add('hidden');
                } else {
                    videoNew.pause();
                    videoOverlayNew.classList.remove('hidden');
                }
            });

            videoNew.addEventListener('pause', () => {
                videoOverlayNew.classList.remove('hidden');
            });

            videoNew.addEventListener('play', () => {
                videoOverlayNew.classList.add('hidden');
            });
        }

        // Observer para carregar vídeos conforme aparecem na tela (lazy load)
        const videoObservers = [];
    </script>
</body>
</html>
