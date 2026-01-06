import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../theme/app_colors.dart';
import '../services/api_service.dart';
import 'whatsapp_config_screen.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final ApiService _api = ApiService();
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _slugController = TextEditingController();

  // State for operating days
  final Map<int, String> _dayMap = {1: 'Seg', 2: 'Ter', 3: 'Qua', 4: 'Qui', 5: 'Sex', 6: 'Sáb', 0: 'Dom'};
  Set<int> _selectedDays = {};

  bool _isLoading = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    setState(() => _isLoading = true);
    try {
      final data = await _api.getSettings();
      setState(() {
        _nameController.text = data['name'] ?? '';
        _phoneController.text = data['phone'] ?? '';
        _addressController.text = data['address'] ?? '';
        _slugController.text = data['booking_link_slug'] ?? '';

        // Parse operating_days
        if (data['operating_days'] != null && data['operating_days'].isNotEmpty) {
          _selectedDays = data['operating_days'].split(',').map(int.parse).toSet();
        }

        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      // Handle error
    }
  }

  Future<void> _saveSettings() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);
    try {
      final operatingDaysString = _selectedDays.join(',');
      await _api.updateSettings({
        'name': _nameController.text,
        'phone': _phoneController.text,
        'address': _addressController.text,
        'operating_days': operatingDaysString,
        'booking_link_slug': _slugController.text,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Configurações salvas com sucesso!'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erro ao salvar: ${e.toString()}'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
    setState(() => _isSaving = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Configurações da Unidade'),
        actions: [
          if (!_isLoading)
            TextButton.icon(
              onPressed: _isSaving ? null : _saveSettings,
              icon: _isSaving ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.save),
              label: const Text('Salvar'),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
            onRefresh: _loadSettings,
            child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildShopDetailsCard(),
                      const SizedBox(height: 24),
                      _buildBookingLinkCard(),
                      const SizedBox(height: 24),
                      _buildWhatsappCard(),
                    ],
                  ),
                ),
              ),
          ),
    );
  }
  
  Widget _buildShopDetailsCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildCardTitle(Icons.store, 'Dados da Barbearia'),
            const SizedBox(height: 24),
            TextFormField(controller: _nameController, decoration: const InputDecoration(labelText: 'Nome da Barbearia', prefixIcon: Icon(Icons.business)), validator: (v) => v?.isEmpty ?? true ? 'Campo obrigatório' : null),
            const SizedBox(height: 16),
            TextFormField(controller: _phoneController, decoration: const InputDecoration(labelText: 'Telefone', prefixIcon: Icon(Icons.phone)), keyboardType: TextInputType.phone),
            const SizedBox(height: 16),
            TextFormField(controller: _addressController, decoration: const InputDecoration(labelText: 'Endereço', prefixIcon: Icon(Icons.location_on)), maxLines: 2),
            const SizedBox(height: 24),
            const Text("Dias de Funcionamento", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8.0,
              children: _dayMap.entries.map((entry) {
                final dayIndex = entry.key;
                final dayName = entry.value;
                final isSelected = _selectedDays.contains(dayIndex);
                return FilterChip(
                  label: Text(dayName),
                  selected: isSelected,
                  onSelected: (bool selected) {
                    setState(() {
                      if (selected) {
                        _selectedDays.add(dayIndex);
                      } else {
                        _selectedDays.remove(dayIndex);
                      }
                    });
                  },
                  selectedColor: AppColors.primaryGold.withOpacity(0.8),
                  checkmarkColor: AppColors.black,
                );
              }).toList(),
            )
          ],
        ),
      ),
    );
  }

  Widget _buildBookingLinkCard() {
    final slug = _slugController.text.trim();
    final fullUrl = "https://revexa.com.br/agendar/$slug";

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildCardTitle(Icons.link, 'Link de Agendamento'),
            const SizedBox(height: 24),
            TextFormField(
              controller: _slugController,
              decoration: InputDecoration(
                labelText: 'URL Amigável',
                prefixIcon: Icon(Icons.public),
                hintText: 'ex: minha-barbearia',
                helperText: 'Apenas letras minúsculas, números e hífens.'
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return null; // Slug is optional
                if (!RegExp(r'^[a-z0-9-]+$').hasMatch(v)) {
                  return 'Formato inválido.';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            if (slug.isNotEmpty) ...[
              const Text("Link gerado:", style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Row(
                children: [
                  Expanded(child: Text(fullUrl, style: TextStyle(color: Colors.blue, decoration: TextDecoration.underline))),
                  IconButton(
                    icon: Icon(Icons.copy, size: 20),
                    onPressed: () {
                      Clipboard.setData(ClipboardData(text: fullUrl));
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Link copiado!')));
                    },
                  ),
                ],
              ),
            ]
          ],
        ),
      ),
    );
  }
  
  Widget _buildWhatsappCard() {
    return Card(
      child: InkWell(
        onTap: () {
          Navigator.push(context, MaterialPageRoute(builder: (context) => const WhatsAppConfigScreen()));
        },
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Row(
            children: [
              _buildIconContainer(Icons.message, Colors.green),
              const SizedBox(width: 16),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Integração WhatsApp', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    SizedBox(height: 4),
                    Text('Conecte seu WhatsApp para enviar notificações', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios, size: 16, color: AppColors.textSecondary),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCardTitle(IconData icon, String title) {
    return Row(
      children: [
        _buildIconContainer(icon, AppColors.primaryGold),
        const SizedBox(width: 16),
        Text(title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
      ],
    );
  }

  Widget _buildIconContainer(IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.2),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Icon(icon, color: color, size: 32),
    );
  }


  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _slugController.dispose();
    super.dispose();
  }
}

