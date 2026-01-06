# REVEXA Sistemas - Website Institucional

Website institucional moderno e responsivo para a REVEXA Sistemas, focado em pequenas empresas.

## 🚀 Tecnologias Utilizadas

- **Backend:** PHP 7.4+
- **Banco de Dados:** SQLite3 (PDO)
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Ícones:** Font Awesome 6
- **Fontes:** Google Fonts (Inter)

## 📁 Estrutura do Projeto

```
REVEXA/
├── admin/
│   └── index.php          # Painel administrativo
├── assets/
│   ├── css/
│   │   └── style.css      # Estilos principais
│   ├── js/
│   │   └── main.js        # JavaScript principal
│   └── images/
│       └── portfolio/     # Imagens do portfólio
├── database/
│   └── revexa.db          # Banco de dados SQLite (gerado automaticamente)
├── includes/
│   └── Database.php       # Classe de conexão com banco de dados
├── .htaccess              # Configurações Apache
├── 404.php                # Página de erro 404
├── 500.php                # Página de erro 500
├── index.php              # Página principal
└── README.md              # Documentação
```

## 🎨 Paleta de Cores

| Cor | Código | Uso |
|-----|--------|-----|
| Primary | `#6366f1` | Botões, links, destaques |
| Secondary | `#ec4899` | Gradientes, acentos |
| Dark | `#1f2937` | Textos, fundos escuros |
| Light | `#f9fafb` | Fundos claros |

## 📦 Instalação

1. Clone ou faça upload dos arquivos para o servidor
2. Certifique-se que o PHP 7.4+ está instalado
3. Verifique se a extensão PDO SQLite está habilitada
4. Acesse o site pelo navegador - o banco de dados será criado automaticamente

## 🔧 Configuração

### Servidor Local (XAMPP/WAMP)
Coloque a pasta `REVEXA` em `htdocs` e acesse: `http://localhost/REVEXA`

### Servidor de Produção
Faça upload para: `oticaemfoco.com.br/sistema/REVEXA`

### Painel Administrativo

Acesse: `/admin`

**Credenciais padrão:**
- Usuário: `admin`
- Senha: `revexa2024`

⚠️ **Importante:** Altere as credenciais no arquivo `admin/index.php` antes de colocar em produção!

## 📋 Funcionalidades

### Site Principal
- [x] Design responsivo (Mobile-First)
- [x] Navegação suave por âncoras
- [x] Hero Section com estatísticas
- [x] Seção Sobre com missão e visão
- [x] Cards de serviços dinâmicos
- [x] Diferenciais da empresa
- [x] Portfólio com filtros por categoria
- [x] Formulário de contato funcional
- [x] Footer com newsletter

### Painel Administrativo
- [x] Dashboard com estatísticas
- [x] CRUD de Serviços
- [x] CRUD de Portfólio
- [x] CRUD de Diferenciais
- [x] Gerenciamento de Contatos

## 🔒 Segurança

- Prepared Statements (PDO) para prevenção de SQL Injection
- Escape de HTML (htmlspecialchars) para prevenção de XSS
- Headers de segurança no .htaccess
- Proteção de diretórios sensíveis
- Sessões para autenticação do admin

## 📱 Responsividade

O site é totalmente responsivo com breakpoints em:
- Desktop: 1200px+
- Tablet: 768px - 1024px
- Mobile: até 768px

## 🌐 SEO

- Meta tags otimizadas
- Estrutura semântica HTML5
- URLs amigáveis via .htaccess

## 📄 Licença

Projeto desenvolvido para REVEXA Sistemas.

---

Desenvolvido com ❤️ por REVEXA Sistemas
