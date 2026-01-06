import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'dart:async';

class WhatsAppConfigScreen extends StatefulWidget {
  const WhatsAppConfigScreen({super.key});

  @override
  State<WhatsAppConfigScreen> createState() => _WhatsAppConfigScreenState();
}

class _WhatsAppConfigScreenState extends State<WhatsAppConfigScreen> {
  String? qrCode;
  bool isConnected = false;
  bool isLoading = false;
  String? phoneNumber;
  String statusMessage = 'Aguardando conexão...';
  Timer? _statusTimer;

  final String serverUrl = 'https://revexa.com.br/whatsapp';

  @override
  void initState() {
    super.initState();
    _checkStatus();
    _startStatusPolling();
  }

  @override
  void dispose() {
    _statusTimer?.cancel();
    super.dispose();
  }

  void _startStatusPolling() {
    _statusTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      if (!isConnected) {
        _checkStatus();
      }
    });
  }

  Future<void> _checkStatus() async {
    try {
      final response = await http.get(Uri.parse('$serverUrl/status'));
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        setState(() {
          isConnected = data['connected'] ?? false;
          phoneNumber = data['phoneNumber'];
          qrCode = data['qrCodeBase64'];
          statusMessage = isConnected
              ? 'Conectado!'
              : (qrCode != null
                    ? 'Escaneie o QR Code'
                    : 'Aguardando conexão...');
        });
      }
    } catch (e) {
      setState(() {
        statusMessage = 'Servidor WhatsApp offline';
      });
    }
  }

  Future<void> _generateQR() async {
    setState(() {
      isLoading = true;
      statusMessage = 'Gerando QR Code...';
    });

    try {
      final response = await http.post(Uri.parse('$serverUrl/generate-qr'));
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        setState(() {
          qrCode = data['qrCodeBase64'];
          statusMessage = 'Escaneie o QR Code com seu WhatsApp';
        });
        _startStatusPolling();
      }
    } catch (e) {
      setState(() {
        statusMessage = 'Erro ao gerar QR Code: $e';
      });
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _disconnect() async {
    setState(() {
      isLoading = true;
      statusMessage = 'Desconectando...';
    });

    try {
      await http.post(Uri.parse('$serverUrl/disconnect'));
      setState(() {
        isConnected = false;
        qrCode = null;
        phoneNumber = null;
        statusMessage = 'Desconectado com sucesso';
      });
    } catch (e) {
      setState(() {
        statusMessage = 'Erro ao desconectar: $e';
      });
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _testMessage() async {
    final TextEditingController phoneController = TextEditingController();
    final TextEditingController messageController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Enviar Mensagem de Teste'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: phoneController,
              decoration: const InputDecoration(
                labelText: 'Telefone',
                hintText: '5532999999999',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.phone,
            ),
            const SizedBox(height: 16),
            TextField(
              controller: messageController,
              decoration: const InputDecoration(
                labelText: 'Mensagem',
                hintText: 'Olá! Esta é uma mensagem de teste.',
                border: OutlineInputBorder(),
              ),
              maxLines: 3,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              try {
                final response = await http.post(
                  Uri.parse('$serverUrl/send-message'),
                  headers: {'Content-Type': 'application/json'},
                  body: json.encode({
                    'phone': phoneController.text,
                    'message': messageController.text,
                  }),
                );

                if (response.statusCode == 200) {
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Mensagem enviada com sucesso!'),
                      ),
                    );
                  }
                } else {
                  throw Exception('Erro ao enviar mensagem');
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(
                    context,
                  ).showSnackBar(SnackBar(content: Text('Erro: $e')));
                }
              }
            },
            child: const Text('Enviar'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Configuração WhatsApp')),
      body: Center(
        child: Container(
          constraints: const BoxConstraints(maxWidth: 600),
          padding: const EdgeInsets.all(24),
          child: SingleChildScrollView(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.message,
                  size: 80,
                  color: isConnected ? Colors.green : Colors.grey,
                ),
                const SizedBox(height: 24),
                Text(
                  isConnected ? 'WhatsApp Conectado!' : 'WhatsApp Desconectado',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: isConnected ? Colors.green : Colors.grey,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  statusMessage,
                  style: Theme.of(context).textTheme.bodyMedium,
                  textAlign: TextAlign.center,
                ),
                if (phoneNumber != null) ...[
                  const SizedBox(height: 8),
                  Text(
                    'Número: $phoneNumber',
                    style: Theme.of(
                      context,
                    ).textTheme.bodySmall?.copyWith(color: Colors.green),
                  ),
                ],
                const SizedBox(height: 32),
                if (qrCode != null && !isConnected) ...[
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 10,
                          spreadRadius: 2,
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        Image.memory(
                          base64Decode(qrCode!.split(',')[1]),
                          width: 300,
                          height: 300,
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          '1. Abra o WhatsApp no celular\n'
                          '2. Toque em Menu ou Configurações\n'
                          '3. Toque em Aparelhos conectados\n'
                          '4. Toque em Conectar um aparelho\n'
                          '5. Aponte seu celular para esta tela',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
                if (isLoading)
                  const CircularProgressIndicator()
                else if (!isConnected)
                  ElevatedButton.icon(
                    onPressed: _generateQR,
                    icon: const Icon(Icons.qr_code_2),
                    label: const Text('Gerar QR Code'),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 32,
                        vertical: 16,
                      ),
                    ),
                  )
                else
                  Wrap(
                    spacing: 16,
                    runSpacing: 16,
                    alignment: WrapAlignment.center,
                    children: [
                      ElevatedButton.icon(
                        onPressed: _testMessage,
                        icon: const Icon(Icons.message),
                        label: const Text('Enviar Teste'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green,
                        ),
                      ),
                      ElevatedButton.icon(
                        onPressed: _disconnect,
                        icon: const Icon(Icons.logout),
                        label: const Text('Desconectar'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red,
                        ),
                      ),
                    ],
                  ),
                const SizedBox(height: 32),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.info_outline, size: 20),
                            const SizedBox(width: 8),
                            Text(
                              'Sobre a integração',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          '• Envie lembretes automáticos de agendamento\n'
                          '• Notifique clientes sobre promoções\n'
                          '• Confirme agendamentos por WhatsApp\n'
                          '• Mantenha seu número sempre conectado',
                          style: TextStyle(fontSize: 12, height: 1.6),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
