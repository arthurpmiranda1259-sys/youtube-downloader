<?php
/**
 * REVEXA Sistemas - Website Institucional
 * Single Page Application com navegação por âncoras
 */

require_once __DIR__ . '/includes/Database.php';

// Initialize database and fetch data
$db = Database::getInstance();
$services = $db->fetchAll("SELECT * FROM services ORDER BY id");
$portfolio = $db->fetchAll("SELECT * FROM portfolio ORDER BY id DESC LIMIT 6");
$differentials = $db->fetchAll("SELECT * FROM differentials ORDER BY id");

// Handle contact form submission
$formMessage = '';
$formSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        try {
            $db->insert('contacts', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ]);
            $formMessage = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
            $formSuccess = true;
        } catch (Exception $e) {
            $formMessage = 'Erro ao enviar mensagem. Por favor, tente novamente.';
        }
    } else {
        $formMessage = 'Por favor, preencha todos os campos obrigatórios.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="REVEXA Sistemas - Soluções completas em tecnologia para pequenas empresas. Sistemas, Sites, Apps e Design.">
    <meta name="keywords" content="sistemas, sites, aplicativos, design, PME, pequenas empresas, tecnologia">
    <meta name="author" content="REVEXA Sistemas">
    
    <title>REVEXA Sistemas | Soluções Completas para seu Negócio</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="#home" class="logo">
                <span class="logo-icon"><i class="fas fa-cube"></i></span>
                <span class="logo-text">REVEXA</span>
            </a>
            
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span class="hamburger"></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="#home" class="nav-link active">Início</a></li>
                <li><a href="#about" class="nav-link">Sobre</a></li>
                <li><a href="#services" class="nav-link">Serviços</a></li>
                <li><a href="#differentials" class="nav-link">Diferenciais</a></li>
                <li><a href="#portfolio" class="nav-link">Portfólio</a></li>
                <li><a href="revexa_sistemas/index.php" class="nav-link" style="color: var(--secondary);">Loja</a></li>
                <li><a href="#contact" class="nav-link nav-cta">Contato</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-bg"></div>
        <div class="hero-blobs">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge">
                    <i class="fas fa-rocket"></i> Impulsione seu Negócio
                </span>
                <h1 class="hero-title">
                    Transformamos Ideias em
                    <span class="gradient-text">Realidade Digital</span>
                </h1>
                <p class="hero-subtitle">
                    Não somos apenas uma agência de software. Somos o parceiro estratégico que une <strong>Design Premium</strong> e <strong>Tecnologia de Ponta</strong> para escalar sua empresa.
                </p>
                <div class="hero-buttons">
                    <a href="#contact" class="btn btn-primary btn-glow">
                        <i class="fas fa-paper-plane"></i> Iniciar Projeto
                    </a>
                    <a href="#portfolio" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Ver Cases
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Projetos Entregues</span>
                    </div>
                    <div class="stat-separator"></div>
                    <div class="stat">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Satisfação</span>
                    </div>
                    <div class="stat-separator"></div>
                    <div class="stat">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Suporte</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-graphic-modern">
                    <div class="glass-card card-main">
                        <div class="code-snippet">
                            <div class="dot red"></div>
                            <div class="dot yellow"></div>
                            <div class="dot green"></div>
                            <div class="code-lines">
                                <div class="line w-80"></div>
                                <div class="line w-60"></div>
                                <div class="line w-90"></div>
                                <div class="line w-40"></div>
                            </div>
                        </div>
                        <div class="card-content">
                            <i class="fas fa-check-circle"></i>
                            <span>Sistema Otimizado</span>
                        </div>
                    </div>
                    <div class="glass-card card-float-1">
                        <i class="fas fa-chart-pie"></i>
                        <span>Analytics</span>
                    </div>
                    <div class="glass-card card-float-2">
                        <i class="fas fa-shield-alt"></i>
                        <span>Seguro</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="mouse">
                <div class="wheel"></div>
            </div>
            <span class="arrow"></span>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about section">
        <div class="section-decoration">
            <div class="circle-shape"></div>
            <div class="dots-pattern"></div>
        </div>
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Quem Somos</span>
                <h2 class="section-title">Sobre a <span class="gradient-text">REVEXA</span></h2>
                <p class="section-subtitle">Entendemos os desafios das pequenas empresas e criamos soluções que realmente funcionam.</p>
            </div>
            
            <div class="about-content">
                <div class="about-text">
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Nossa Missão</h3>
                        <p>Democratizar o acesso à tecnologia de qualidade para pequenas empresas, oferecendo soluções completas que impulsionam resultados reais.</p>
                    </div>
                    
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Nossa Visão</h3>
                        <p>Ser a parceira tecnológica de referência para PMEs, reconhecida pela qualidade, inovação e compromisso com o sucesso de nossos clientes.</p>
                    </div>
                    
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Nossa Metodologia</h3>
                        <p>Trabalhamos lado a lado com você para entender suas necessidades específicas. Não existe problema pequeno demais ou grande demais - encontramos a solução ideal.</p>
                    </div>
                </div>
                
                <div class="about-visual">
                    <div class="about-image">
                        <div class="image-decoration"></div>
                        <div class="about-graphic">
                            <i class="fas fa-handshake"></i>
                            <h4>Parceria de Verdade</h4>
                            <p>Seu sucesso é o nosso sucesso</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services section section-alt">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">O que Fazemos</span>
                <h2 class="section-title">Nossos <span class="gradient-text">Serviços</span></h2>
                <p class="section-subtitle">Soluções completas para transformar sua empresa e conquistar mais clientes.</p>
            </div>
            
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="<?= htmlspecialchars($service['icon']) ?>"></i>
                    </div>
                    <h3 class="service-title"><?= htmlspecialchars($service['title']) ?></h3>
                    <p class="service-description"><?= htmlspecialchars($service['description']) ?></p>
                    <?php if (!empty($service['features'])): ?>
                    <ul class="service-features">
                        <?php foreach (explode(',', $service['features']) as $feature): ?>
                        <li><i class="fas fa-check"></i> <?= htmlspecialchars(trim($feature)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <a href="#contact" class="service-link">
                        Saiba mais <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Differentials Section -->
    <section id="differentials" class="differentials section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Por que nos escolher</span>
                <h2 class="section-title">Nossos <span class="gradient-text">Diferenciais</span></h2>
                <p class="section-subtitle">O que nos torna a escolha certa para sua empresa.</p>
            </div>
            
            <div class="differentials-grid">
                <?php foreach ($differentials as $index => $diff): ?>
                <div class="differential-card" style="--delay: <?= $index * 0.1 ?>s">
                    <div class="differential-number"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></div>
                    <div class="differential-icon">
                        <i class="<?= htmlspecialchars($diff['icon']) ?>"></i>
                    </div>
                    <h3 class="differential-title"><?= htmlspecialchars($diff['title']) ?></h3>
                    <p class="differential-description"><?= htmlspecialchars($diff['description']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio section section-alt">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Nosso Trabalho</span>
                <h2 class="section-title">Portfólio de <span class="gradient-text">Sucesso</span></h2>
                <p class="section-subtitle">Conheça alguns dos projetos que transformaram negócios.</p>
            </div>
            
            <div class="portfolio-filter">
                <button class="filter-btn active" data-filter="all">Todos</button>
                <button class="filter-btn" data-filter="Sistemas">Sistemas</button>
                <button class="filter-btn" data-filter="Sites">Sites</button>
                <button class="filter-btn" data-filter="Apps">Apps</button>
                <button class="filter-btn" data-filter="Design">Design</button>
            </div>
            
            <div class="portfolio-grid">
                <?php foreach ($portfolio as $project): ?>
                <div class="portfolio-item" data-category="<?= htmlspecialchars($project['category']) ?>">
                    <div class="portfolio-image">
                        <div class="portfolio-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="portfolio-overlay">
                            <span class="portfolio-category"><?= htmlspecialchars($project['category']) ?></span>
                            <h4 class="portfolio-title"><?= htmlspecialchars($project['title']) ?></h4>
                            <p class="portfolio-client">
                                <i class="fas fa-building"></i> <?= htmlspecialchars($project['client_name']) ?>
                            </p>
                            <button class="portfolio-btn" data-project="<?= $project['id'] ?>">
                                <i class="fas fa-eye"></i> Ver Detalhes
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Pronto para Transformar seu Negócio?</h2>
                <p class="cta-text">Entre em contato agora e descubra como podemos ajudar sua empresa a crescer com soluções tecnológicas sob medida.</p>
                <a href="#contact" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i> Começar Agora
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section section-alt">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Fale Conosco</span>
                <h2 class="section-title">Entre em <span class="gradient-text">Contato</span></h2>
                <p class="section-subtitle">Estamos prontos para ouvir suas necessidades e criar a solução perfeita.</p>
            </div>
            
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>E-mail</h4>
                        <p>contato@revexasistemas.com.br</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4>Telefone</h4>
                        <p>(00) 00000-0000</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h4>WhatsApp</h4>
                        <p>(00) 00000-0000</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Horário</h4>
                        <p>Seg - Sex: 8h às 18h</p>
                    </div>
                    
                    <div class="contact-social">
                        <h4>Siga-nos</h4>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-wrapper">
                    <?php if ($formMessage): ?>
                    <div class="form-message <?= $formSuccess ? 'success' : 'error' ?>">
                        <i class="fas <?= $formSuccess ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($formMessage) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form class="contact-form" method="POST" action="#contact" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nome Completo *</label>
                                <input type="text" id="name" name="name" required placeholder="Seu nome">
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail *</label>
                                <input type="email" id="email" name="email" required placeholder="seu@email.com">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Telefone</label>
                                <input type="tel" id="phone" name="phone" placeholder="(00) 00000-0000">
                            </div>
                            <div class="form-group">
                                <label for="subject">Assunto</label>
                                <select id="subject" name="subject">
                                    <option value="">Selecione...</option>
                                    <option value="Sistemas">Sistemas Personalizados</option>
                                    <option value="Sites">Sites Profissionais</option>
                                    <option value="Apps">Aplicativos Mobile</option>
                                    <option value="Design">Design & Identidade</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Mensagem *</label>
                            <textarea id="message" name="message" rows="5" required placeholder="Conte-nos sobre seu projeto ou necessidade..."></textarea>
                        </div>
                        
                        <button type="submit" name="contact_submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Enviar Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <a href="#home" class="logo">
                        <span class="logo-icon"><i class="fas fa-cube"></i></span>
                        <span class="logo-text">REVEXA</span>
                    </a>
                    <p>Soluções tecnológicas completas para pequenas empresas que querem crescer e se destacar no mercado digital.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Links Rápidos</h4>
                    <ul>
                        <li><a href="#home">Início</a></li>
                        <li><a href="#about">Sobre Nós</a></li>
                        <li><a href="#services">Serviços</a></li>
                        <li><a href="#portfolio">Portfólio</a></li>
                        <li><a href="#contact">Contato</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Serviços</h4>
                    <ul>
                        <li><a href="#services">Sistemas Personalizados</a></li>
                        <li><a href="#services">Sites Profissionais</a></li>
                        <li><a href="#services">Aplicativos Mobile</a></li>
                        <li><a href="#services">Design & Branding</a></li>
                    </ul>
                </div>
                
                <div class="footer-newsletter">
                    <h4>Newsletter</h4>
                    <p>Receba novidades e dicas sobre tecnologia para seu negócio.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Seu e-mail" required>
                        <button type="submit"><i class="fas fa-arrow-right"></i></button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> REVEXA Sistemas. Todos os direitos reservados.</p>
                <div class="footer-legal">
                    <a href="#">Política de Privacidade</a>
                    <a href="#">Termos de Uso</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" aria-label="Voltar ao topo">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
