# 📦 Como Compilar para .EXE

## 🎯 Método Fácil (Recomendado)

```bash
python build_exe.py
```

Pronto! O executável estará em `dist/NeoYT_Downloader.exe`

---

## 🔧 Método Manual

### 1️⃣ Instalar PyInstaller
```bash
pip install pyinstaller
```

### 2️⃣ Compilar
```bash
pyinstaller --onefile --windowed --name NeoYT_Downloader youtube_downloader_gui.py
```

### 3️⃣ Encontrar o executável
```
dist/NeoYT_Downloader.exe  ← Aqui está!
```

---

## ⚙️ Opções Avançadas

### 🎨 Adicionar ícone personalizado
```bash
pyinstaller --onefile --windowed --icon=icon.ico --name NeoYT_Downloader youtube_downloader_gui.py
```

### 📁 Incluir arquivos extras
```bash
pyinstaller --onefile --windowed --add-data "assets:assets" --name NeoYT_Downloader youtube_downloader_gui.py
```

### 🚫 Manter console (para debug)
```bash
pyinstaller --onefile --name NeoYT_Downloader youtube_downloader_gui.py
```
(Remova `--windowed`)

### 📊 Build detalhado (ver dependências)
```bash
pyinstaller --onefile --windowed --name NeoYT_Downloader --log-level DEBUG youtube_downloader_gui.py
```

---

## 🖥️ Compilar no Linux para Windows

**Opção 1: Usando Wine**
```bash
# Instalar Wine
sudo apt install wine wine64

# Instalar Python no Wine
wine python-installer.exe

# Compilar
wine python build_exe.py
```

**Opção 2: Usar VM Windows** (Recomendado)
- Crie uma VM Windows 10/11
- Instale Python
- Execute `python build_exe.py`

**Opção 3: GitHub Actions** (Automático)
- Configure CI/CD para compilar automaticamente

---

## 📋 Checklist de Distribuição

- [ ] Testar o .exe em máquina limpa (sem Python)
- [ ] Verificar se yt-dlp está incluído
- [ ] Testar download de vídeo simples
- [ ] Testar download de playlist
- [ ] Verificar se o ícone aparece corretamente
- [ ] Comprimir com UPX (opcional, reduz tamanho)
- [ ] Criar instalador com NSIS/Inno Setup (opcional)

---

## 🎁 Distribuir

### Opção 1: ZIP Simples
```bash
zip -r NeoYT_Downloader.zip dist/NeoYT_Downloader.exe README.md
```

### Opção 2: Criar Instalador
Use **Inno Setup** (Windows):
```
[Setup]
AppName=NeoYT Downloader
AppVersion=1.0
DefaultDirName={pf}\NeoYT Downloader
OutputBaseFilename=NeoYT_Downloader_Setup

[Files]
Source: "dist\NeoYT_Downloader.exe"; DestDir: "{app}"

[Icons]
Name: "{commondesktop}\NeoYT Downloader"; Filename: "{app}\NeoYT_Downloader.exe"
```

---

## 🐛 Problemas Comuns

### ❌ "Failed to execute script"
- **Causa**: Faltam dependências
- **Solução**: Use `--hidden-import` para adicionar módulos manualmente
```bash
pyinstaller --onefile --windowed --hidden-import=yt_dlp --name NeoYT_Downloader youtube_downloader_gui.py
```

### ❌ Executável muito grande (>100 MB)
- **Solução**: Use UPX para comprimir
```bash
pip install upx
pyinstaller --onefile --windowed --upx-dir=/path/to/upx --name NeoYT_Downloader youtube_downloader_gui.py
```

### ❌ Antivírus bloqueia o .exe
- **Causa**: Falso positivo comum em executáveis PyInstaller
- **Solução**: 
  1. Assine digitalmente o executável
  2. Envie para análise de falso positivo
  3. Distribua também o código-fonte

### ❌ Demora muito para abrir
- **Causa**: Normal, PyInstaller extrai arquivos temporariamente
- **Solução**: Use `--onedir` ao invés de `--onefile` (mais rápido)

---

## 📊 Comparação de Tamanhos

| Método | Tamanho Típico |
|--------|----------------|
| `--onefile` | 15-30 MB |
| `--onefile --upx-dir` | 10-20 MB |
| `--onedir` | 50-80 MB (pasta) |

---

## 🚀 Alternativas ao PyInstaller

### cx_Freeze
```bash
pip install cx_Freeze
cxfreeze youtube_downloader_gui.py --target-dir dist
```

### Nuitka (mais rápido)
```bash
pip install nuitka
python -m nuitka --onefile --windows-disable-console youtube_downloader_gui.py
```

### py2exe (somente Windows)
```bash
pip install py2exe
python setup_py2exe.py
```

---

## 💡 Dicas Profissionais

1. **Sempre teste em máquina limpa** (sem Python instalado)
2. **Adicione ícone personalizado** para parecer profissional
3. **Crie instalador** se for distribuir publicamente
4. **Assine digitalmente** para evitar warnings de segurança
5. **Documente requisitos** (Windows 10+, RAM mínima, etc.)

---

## 📞 Suporte

Problemas? 
- Verifique logs: `build/NeoYT_Downloader/warn-NeoYT_Downloader.txt`
- Execute com console: remova `--windowed` para ver erros
- Aumente verbosidade: `--log-level DEBUG`
