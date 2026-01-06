import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../theme/app_colors.dart';

class UpdateChecker extends StatefulWidget {
  final Widget child;
  const UpdateChecker({required this.child, super.key});

  @override
  State<UpdateChecker> createState() => _UpdateCheckerState();
}

class _UpdateCheckerState extends State<UpdateChecker> {
  final ApiService _api = ApiService();
  bool _hasChecked = false;

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(seconds: 3), _checkForUpdates);
  }

  Future<void> _checkForUpdates() async {
    if (_hasChecked) return;
    _hasChecked = true;

    try {
      final response = await _api.checkVersion();
      final currentBuild = 1; // Incrementar a cada nova versão
      final serverBuild = response['build'] ?? 1;

      if (serverBuild > currentBuild && mounted) {
        _showUpdateDialog(
          response['version'] ?? '2.0.0',
          response['apk_url'] ?? '',
          response['message'] ?? 'Nova versão disponível!',
          response['force_update'] ?? false,
        );
      }
    } catch (e) {
      print('Update check failed: $e');
    }
  }

  void _showUpdateDialog(
    String version,
    String apkUrl,
    String message,
    bool forceUpdate,
  ) {
    showDialog(
      context: context,
      barrierDismissible: !forceUpdate,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.system_update, color: AppColors.primaryGold),
            const SizedBox(width: 12),
            const Text('Atualização Disponível'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Versão $version disponível!'),
            const SizedBox(height: 8),
            Text(
              message,
              style: const TextStyle(color: AppColors.textSecondary),
            ),
          ],
        ),
        actions: [
          if (!forceUpdate)
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Depois'),
            ),
          ElevatedButton.icon(
            onPressed: () async {
              final uri = Uri.parse(apkUrl);
              if (await canLaunchUrl(uri)) {
                await launchUrl(uri, mode: LaunchMode.externalApplication);
              }
              if (context.mounted && !forceUpdate) {
                Navigator.pop(context);
              }
            },
            icon: const Icon(Icons.download),
            label: const Text('Baixar Agora'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) => widget.child;
}
