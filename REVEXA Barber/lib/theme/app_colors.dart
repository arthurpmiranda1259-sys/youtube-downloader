import 'package:flutter/material.dart';

class AppColors {
  // Cores Primárias - Paleta Dourada Luxuosa
  static const Color primaryGold = Color(0xFFD4AF37);
  static const Color darkGold = Color(0xFFB8860B);
  static const Color lightGold = Color(0xFFFFD700);

  // Cores de Fundo - Tons Escuros Elegantes
  static const Color background = Color(0xFF0F0F0F);
  static const Color backgroundDark = Color(0xFF0F0F0F);
  static const Color backgroundLight = Color(0xFF1A1A1A);
  static const Color surfaceDark = Color(0xFF252525);
  static const Color surfaceLight = Color(0xFF2F2F2F);

  // Cores de Texto
  static const Color textPrimary = Color(0xFFFFFFFF);
  static const Color textSecondary = Color(0xFFB3B3B3);
  static const Color textTertiary = Color(0xFF808080);

  // Cores de Estado
  static const Color success = Color(0xFF10B981);
  static const Color error = Color(0xFFEF4444);
  static const Color warning = Color(0xFFF59E0B);
  static const Color info = Color(0xFF3B82F6);

  // Cores Neutras
  static const Color white = Color(0xFFFFFFFF);
  static const Color black = Color(0xFF000000);
  static const Color borderGray = Color(0xFF404040);
  static const Color lightGray = Color(0xFF505050);

  // Gradientes
  static const LinearGradient goldGradient = LinearGradient(
    colors: [darkGold, primaryGold, lightGold],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient darkGradient = LinearGradient(
    colors: [backgroundDark, backgroundLight],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );
}
