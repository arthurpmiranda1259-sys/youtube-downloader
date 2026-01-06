# REVEXA DENTAL - Sistema de Prontuário Odontológico

Sistema completo de gestão de clínica odontológica desenvolvido em PHP com SQLite.

## 📋 Funcionalidades

✅ **Gestão de Pacientes**
- Cadastro completo com dados pessoais e contato
- Busca CEP automática via ViaCEP
- Histórico de consultas

✅ **Prontuário Eletrônico**
- Anamnese completa
- Odontograma digital interativo
- Evoluções clínicas
- Plano de tratamento
- Upload de documentos e imagens

✅ **Agenda**
- Calendário de agendamentos
- Filtro por dentista e data
- Status de confirmação
- Lembretes visuais

✅ **Financeiro**
- Contas a receber
- Contas a pagar
- Controle de formas de pagamento
- Relatórios financeiros

✅ **Gestão de Procedimentos**
- Tabela de preços
- Valores para particular e convênio

✅ **Controle de Usuários**
- 3 níveis de acesso: Admin, Dentista, Recepcionista
- Log de auditoria
- Permissões granulares

✅ **Relatórios**
- Financeiro
- Produção por dentista
- Top pacientes

## 🚀 Instalação

### 1. Fazer Upload dos Arquivos

Envie todos os arquivos para o diretório:
```
oticaemfoco.com.br/sistema/REVEXA/revexa_sistemas/dentista/
```

### 2. Configurar Permissões

Execute via SSH ou File Manager:
```bash
chmod 755 dentista/
chmod 777 dentista/config/
chmod 777 dentista/uploads/
```

### 3. Acessar o Sistema

Acesse no navegador:
```
https://oticaemfoco.com.br/sistema/REVEXA/revexa_sistemas/dentista/
```

### 4. Login Inicial

**Usuário:** admin@revexa.com.br  
**Senha:** admin123

⚠️ **IMPORTANTE:** Altere a senha padrão após o primeiro acesso!

## 📁 Estrutura de Arquivos

```
dentista/
├── index.php              # Página de login
├── dashboard.php          # Dashboard principal
├── logout.php            # Logout
├── config/
│   ├── config.php        # Configurações gerais
│   ├── database.sql      # Schema do banco
│   └── dentista.db       # Banco SQLite (criado automaticamente)
├── includes/
│   ├── header.php        # Header padrão
│   └── footer.php        # Footer padrão
├── modules/
│   ├── pacientes.php     # CRUD de pacientes
│   ├── agenda.php        # Sistema de agendamentos
│   ├── prontuario.php    # Prontuário eletrônico
│   ├── procedimentos.php # Tabela de procedimentos
│   ├── financeiro.php    # Gestão financeira
│   ├── usuarios.php      # Gestão de usuários
│   └── relatorios.php    # Relatórios
├── assets/
│   ├── css/
│   │   └── style.css     # Estilos responsivos
│   └── js/
│       └── main.js       # JavaScript principal
└── uploads/              # Diretório para uploads
```

## 🔐 Níveis de Acesso

### Recepcionista
- Visualizar pacientes
- Gerenciar agenda
- Registrar recebimentos
- Visualizar relatórios básicos

### Dentista
- Todas as permissões do Recepcionista
- Acessar e editar prontuários
- Criar planos de tratamento
- Gerenciar procedimentos

### Administrador
- Acesso total ao sistema
- Gerenciar usuários
- Relatórios completos
- Logs de auditoria

## 🛠️ Requisitos do Servidor

- PHP 7.4 ou superior
- Extensão PDO SQLite habilitada
- mod_rewrite habilitado (Apache)

## 💾 Backup

O banco de dados SQLite está em:
```
config/dentista.db
```

**Recomendação:** Faça backup diário deste arquivo!

## 🔧 Configurações

Edite `config/config.php` para ajustar:
- URL base do sistema
- Tamanho máximo de uploads
- Timezone
- Itens por página

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- Desktop
- Tablets
- Smartphones

## ⚡ Desempenho

- SQLite: rápido e sem necessidade de servidor MySQL
- Assets otimizados
- Carregamento assíncrono
- Cache de consultas

## 🆘 Suporte

Em caso de problemas:

1. Verifique as permissões dos diretórios
2. Confira se o PHP está habilitado
3. Verifique o log de erros do servidor
4. Entre em contato com o desenvolvedor

## 📝 Notas Importantes

- Sempre faça backup antes de atualizar
- Teste em ambiente de homologação primeiro
- Mantenha senhas seguras
- Revise os logs de auditoria periodicamente

## 🔄 Próximas Atualizações (Roadmap)

- [ ] Integração WhatsApp para lembretes
- [ ] Exportação de dados (PDF/Excel)
- [ ] Modo offline com sincronização
- [ ] Relatórios gráficos avançados
- [ ] Receituário eletrônico
- [ ] Atestados personalizados

## 📄 Licença

Sistema desenvolvido por **NeoStark** para uso interno.

---

**Versão:** 1.0.0  
**Data:** Dezembro 2024
