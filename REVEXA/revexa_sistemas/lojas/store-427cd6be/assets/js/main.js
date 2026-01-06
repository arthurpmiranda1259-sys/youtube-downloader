// ============================================================
// REVEXA DENTAL - JavaScript Principal
// ============================================================

// Toggle da Sidebar (Mobile)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

// Fechar sidebar ao clicar fora (mobile)
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});

// Loading Overlay
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

// Confirmação de exclusão
function confirmDelete(message) {
    return confirm(message || 'Tem certeza que deseja excluir este registro?');
}

// Máscaras de input
function maskCPF(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    input.value = value;
}

function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 10) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }
    input.value = value;
}

function maskCEP(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    input.value = value;
}

function maskCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    value = (parseInt(value) / 100).toFixed(2);
    input.value = value.replace('.', ',');
}

function maskDate(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '$1/$2');
    value = value.replace(/(\d{2})(\d)/, '$1/$2');
    input.value = value;
}

// Auto-aplicar máscaras
document.addEventListener('DOMContentLoaded', function() {
    // CPF
    document.querySelectorAll('input[data-mask="cpf"]').forEach(input => {
        input.addEventListener('input', () => maskCPF(input));
    });
    
    // Telefone
    document.querySelectorAll('input[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', () => maskPhone(input));
    });
    
    // CEP
    document.querySelectorAll('input[data-mask="cep"]').forEach(input => {
        input.addEventListener('input', () => maskCEP(input));
    });
    
    // Moeda
    document.querySelectorAll('input[data-mask="currency"]').forEach(input => {
        input.addEventListener('input', () => maskCurrency(input));
    });
    
    // Data
    document.querySelectorAll('input[data-mask="date"]').forEach(input => {
        input.addEventListener('input', () => maskDate(input));
    });
});

// Buscar CEP
async function buscarCEP(cep, callback) {
    cep = cep.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();
        
        if (!data.erro) {
            callback(data);
        } else {
            alert('CEP não encontrado!');
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
        alert('Erro ao buscar CEP. Tente novamente.');
    } finally {
        hideLoading();
    }
}

// Validações
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let digit = 11 - (sum % 11);
    if (digit >= 10) digit = 0;
    if (parseInt(cpf.charAt(9)) !== digit) return false;
    
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    digit = 11 - (sum % 11);
    if (digit >= 10) digit = 0;
    if (parseInt(cpf.charAt(10)) !== digit) return false;
    
    return true;
}

// Calcular idade a partir da data de nascimento
function calcularIdade(dataNascimento) {
    const hoje = new Date();
    const nascimento = new Date(dataNascimento);
    let idade = hoje.getFullYear() - nascimento.getFullYear();
    const mes = hoje.getMonth() - nascimento.getMonth();
    
    if (mes < 0 || (mes === 0 && hoje.getDate() < nascimento.getDate())) {
        idade--;
    }
    
    return idade;
}

// Formatar data para exibição
function formatarData(data) {
    if (!data) return '';
    const partes = data.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

// Print
function printDiv(divId) {
    const conteudo = document.getElementById(divId).innerHTML;
    const estilo = '<link rel="stylesheet" href="assets/css/style.css">';
    
    const win = window.open('', '', 'height=700,width=900');
    win.document.write('<html><head>');
    win.document.write(estilo);
    win.document.write('</head><body>');
    win.document.write(conteudo);
    win.document.write('</body></html>');
    win.document.close();
    
    setTimeout(() => {
        win.print();
        win.close();
    }, 250);
}

// Autocomplete simples
function autocomplete(input, items) {
    let currentFocus;
    
    input.addEventListener('input', function(e) {
        const val = this.value;
        closeAllLists();
        
        if (!val) return false;
        currentFocus = -1;
        
        const list = document.createElement('div');
        list.setAttribute('id', this.id + '-autocomplete-list');
        list.setAttribute('class', 'autocomplete-items');
        this.parentNode.appendChild(list);
        
        for (let i = 0; i < items.length; i++) {
            if (items[i].toLowerCase().includes(val.toLowerCase())) {
                const item = document.createElement('div');
                item.innerHTML = items[i].replace(new RegExp(val, 'gi'), '<strong>$&</strong>');
                item.innerHTML += `<input type='hidden' value='${items[i]}'>`;
                
                item.addEventListener('click', function(e) {
                    input.value = this.getElementsByTagName('input')[0].value;
                    closeAllLists();
                });
                
                list.appendChild(item);
            }
        }
    });
    
    input.addEventListener('keydown', function(e) {
        let x = document.getElementById(this.id + '-autocomplete-list');
        if (x) x = x.getElementsByTagName('div');
        
        if (e.keyCode == 40) { // Down
            currentFocus++;
            addActive(x);
        } else if (e.keyCode == 38) { // Up
            currentFocus--;
            addActive(x);
        } else if (e.keyCode == 13) { // Enter
            e.preventDefault();
            if (currentFocus > -1 && x) {
                x[currentFocus].click();
            }
        }
    });
    
    function addActive(x) {
        if (!x) return false;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        x[currentFocus].classList.add('autocomplete-active');
    }
    
    function removeActive(x) {
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove('autocomplete-active');
        }
    }
    
    function closeAllLists(elmnt) {
        const x = document.getElementsByClassName('autocomplete-items');
        for (let i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != input) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    
    document.addEventListener('click', function(e) {
        closeAllLists(e.target);
    });
}

// Copiar para clipboard
function copyToClipboard(text) {
    const temp = document.createElement('textarea');
    temp.value = text;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    
    alert('Copiado!');
}

// Exportar tabela para CSV
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename || 'export.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
