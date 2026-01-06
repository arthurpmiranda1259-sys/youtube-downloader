# Deploy Scripts - REVEXA Barber

## Scripts Disponíveis

### 1. `deploy.py` - Deploy Web + API
Deploy completo do sistema web (frontend Flutter + backend PHP).

```bash
python3 deploy/deploy.py
```

**O que faz:**
- ✅ Build do Flutter Web (`flutter build web --release`)
- ✅ Upload do arquivo `api.php`
- ✅ Upload de todos os arquivos do `build/web/`
- ✅ Deploy para: https://revexa.com.br/revexa_sistemas/Sistemas/Revexa_Barber

---

### 2. `deploy_apk.py` - Deploy APK Android
Build e deploy do aplicativo Android.

```bash
python3 deploy/deploy_apk.py
```

**O que faz:**
- ✅ Build do APK Android (`flutter build apk --release`)
- ✅ Upload do arquivo `app-release.apk` renomeado para `revexa-barber.apk`
- ✅ Deploy para: https://revexa.com.br/revexa_sistemas/Sistemas/Revexa_Barber/revexa-barber.apk

---

### 3. `deploy_full.py` - Deploy Completo (Web + APK)
Deploy interativo com opções.

```bash
python3 deploy/deploy_full.py
```

**Opções:**
1. Web apenas
2. APK apenas  
3. Web + APK (completo)

**O que faz:**
- ✅ Pergunta o que você quer fazer
- ✅ Executa os builds necessários
- ✅ Faz upload de tudo via FTP

---

## Configuração FTP

Os scripts usam as seguintes credenciais (já configuradas):

```python
FTP_HOST = "ftp.revexa.com.br"
FTP_USER = "revexa"
FTP_PASS = "D0ming0s"
FTP_TARGET_DIR = "revexa_sistemas/Sistemas/Revexa_Barber/"
```

---

## URLs de Acesso

**Sistema Web:**
https://revexa.com.br/revexa_sistemas/Sistemas/Revexa_Barber

**Download APK:**
https://revexa.com.br/revexa_sistemas/Sistemas/Revexa_Barber/revexa-barber.apk

**API:**
https://revexa.com.br/revexa_sistemas/Sistemas/Revexa_Barber/api.php

---

## Requisitos

- Flutter SDK instalado
- Python 3.x
- Acesso FTP configurado
- Android SDK (para build do APK)

---

## Build Local (sem deploy)

### Web
```bash
flutter build web --release --base-href /revexa_sistemas/Sistemas/Revexa_Barber/
```

### APK
```bash
flutter build apk --release
```

### APK Split (múltiplas arquiteturas)
```bash
flutter build apk --release --split-per-abi
```

Isso gera APKs otimizados:
- `app-armeabi-v7a-release.apk` (32-bit ARM)
- `app-arm64-v8a-release.apk` (64-bit ARM)
- `app-x86_64-release.apk` (64-bit Intel)

---

## Localização dos Arquivos

**Web Build:**
```
build/web/
```

**APK Build:**
```
build/app/outputs/flutter-apk/app-release.apk
```

**API:**
```
backend/api.php
```

---

## Troubleshooting

### Erro no build do APK
```bash
cd android
./gradlew clean
cd ..
flutter clean
flutter pub get
flutter build apk --release
```

### Erro no FTP
Verifique:
- Conexão com a internet
- Credenciais FTP
- Permissões da pasta remota

### APK não instala
- Ative "Fontes desconhecidas" no Android
- Verifique se a versão do Android é compatível (min: 21)
- Certifique-se que o APK está assinado (debug ou release)

---

## Informações do APK

**Application ID:** `com.revexa.barber`  
**Version Code:** `1`  
**Version Name:** `2.0.0`  
**Min SDK:** `21` (Android 5.0)  
**Target SDK:** `34` (Android 14)

---

## Notas

- O APK é assinado com chave de debug por padrão
- Para produção, gere uma chave de release e configure no `android/app/build.gradle.kts`
- O build do APK pode demorar alguns minutos
- O tamanho típico do APK é ~25-40 MB
