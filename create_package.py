"""
Script para criar pacote de distribuição COMPLETO
Inclui exe + instalador + documentação
"""
import os
import shutil
import zipfile
from pathlib import Path

def create_distribution_package():
    """Cria pacote completo para distribuição"""
    
    print("=" * 60)
    print("📦 Criando Pacote de Distribuição Completo")
    print("=" * 60)
    
    # Diretório de distribuição
    dist_folder = Path("dist")
    package_name = "NeoYT_Downloader_Portable_v1.0"
    package_dir = dist_folder / package_name
    
    # Limpar e criar
    if package_dir.exists():
        shutil.rmtree(package_dir)
    package_dir.mkdir(parents=True, exist_ok=True)
    
    print(f"\n📁 Criando estrutura em: {package_dir}")
    
    # Lista de arquivos para incluir
    files_to_copy = [
        ("dist/NeoYT_Downloader_Portable.exe", "NeoYT_Downloader_Portable.exe"),
        ("INSTALADOR_COMPLETO.bat", "INSTALADOR_COMPLETO.bat"),
        ("LEIA-ME.txt", "LEIA-ME.txt"),
    ]
    
    # Copiar arquivos
    print("\n📋 Copiando arquivos:")
    for src, dst in files_to_copy:
        src_path = Path(src)
        dst_path = package_dir / dst
        
        if src_path.exists():
            shutil.copy2(src_path, dst_path)
            print(f"   ✅ {dst}")
        else:
            print(f"   ⚠️  {dst} não encontrado (pulando)")
    
    # Criar arquivo de versão
    version_file = package_dir / "VERSION.txt"
    with open(version_file, "w", encoding="utf-8") as f:
        f.write("NeoYT Downloader Portable\n")
        f.write("Versão: 1.0\n")
        f.write("Build: Portable Edition\n")
        f.write("Data: 2026-01-05\n")
    print("   ✅ VERSION.txt")
    
    # Criar arquivo de início rápido
    quickstart = package_dir / "INICIO_RAPIDO.txt"
    with open(quickstart, "w", encoding="utf-8") as f:
        f.write("═══════════════════════════════════════════════\n")
        f.write("  INÍCIO RÁPIDO - NeoYT Downloader\n")
        f.write("═══════════════════════════════════════════════\n\n")
        f.write("MÉTODO 1 (Recomendado):\n")
        f.write("   1. Execute: INSTALADOR_COMPLETO.bat\n")
        f.write("   2. Aguarde instalação automática\n")
        f.write("   3. Pronto!\n\n")
        f.write("MÉTODO 2 (Direto):\n")
        f.write("   1. Execute: NeoYT_Downloader_Portable.exe\n")
        f.write("   2. Aceite instalar FFmpeg quando perguntado\n")
        f.write("   3. Pronto!\n\n")
        f.write("═══════════════════════════════════════════════\n")
        f.write("💡 Para instruções completas: veja LEIA-ME.txt\n")
        f.write("═══════════════════════════════════════════════\n")
    print("   ✅ INICIO_RAPIDO.txt")
    
    # Compactar tudo em ZIP
    print(f"\n📦 Compactando em ZIP...")
    zip_path = dist_folder / f"{package_name}.zip"
    
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for file_path in package_dir.rglob('*'):
            if file_path.is_file():
                arcname = file_path.relative_to(dist_folder)
                zipf.write(file_path, arcname)
    
    # Calcular tamanhos
    package_size = sum(f.stat().st_size for f in package_dir.rglob('*') if f.is_file())
    zip_size = zip_path.stat().st_size
    
    print("\n" + "=" * 60)
    print("✅ PACOTE CRIADO COM SUCESSO!")
    print("=" * 60)
    print(f"\n📦 Pacote: {zip_path}")
    print(f"📊 Tamanho descompactado: {package_size / 1024 / 1024:.1f} MB")
    print(f"📊 Tamanho do ZIP: {zip_size / 1024 / 1024:.1f} MB")
    print(f"\n📂 Conteúdo:")
    for file_path in sorted(package_dir.rglob('*')):
        if file_path.is_file():
            rel_path = file_path.relative_to(package_dir)
            size = file_path.stat().st_size / 1024
            print(f"   • {rel_path} ({size:.1f} KB)")
    
    print("\n🚀 PRÓXIMOS PASSOS:")
    print(f"   1. Extraia: {package_name}.zip")
    print(f"   2. Distribua a pasta ou o ZIP")
    print("   3. Usuários executam: INSTALADOR_COMPLETO.bat")
    print("   4. Pronto - ZERO configuração manual!")
    
    print("\n💡 DISTRIBUIÇÃO:")
    print("   • Google Drive / Dropbox / OneDrive")
    print("   • GitHub Releases")
    print("   • Seu próprio site")
    print("   • Pen drive / USB")
    print("=" * 60 + "\n")

if __name__ == "__main__":
    create_distribution_package()
