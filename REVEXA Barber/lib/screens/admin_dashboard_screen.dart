import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../services/api_service.dart';
import '../widgets/responsive_layout.dart';
import '../widgets/stat_card.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  Future<void> _logout(BuildContext context) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user');
    if (context.mounted) {
      Navigator.of(context).pushNamedAndRemoveUntil('/', (route) => false);
    }
  }

  bool _isCreatingBarbershop = false;
  final ApiService _api = ApiService();
  bool _isLoading = true;
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  Future<void> _loadStats() async {
    setState(() => _isLoading = true);
    try {
      final data = await _api.getDashboard();
      setState(() {
        _stats = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  void _showCreateBarbershopDialog() {
    final nameController = TextEditingController();
    final passwordController = TextEditingController();
    final barbershopNameController = TextEditingController();
    final phoneController = TextEditingController();

    void formatPhone(String value) {
      final numbers = value.replaceAll(RegExp(r'[^0-9]'), '');
      String formatted = '';

      if (numbers.isNotEmpty) {
        formatted = '(';
        formatted += numbers.substring(
          0,
          numbers.length > 2 ? 2 : numbers.length,
        );
        if (numbers.length > 2) {
          formatted += ') ';
          formatted += numbers.substring(
            2,
            numbers.length > 7 ? 7 : numbers.length,
          );
          if (numbers.length > 7) {
            formatted += '-';
            formatted += numbers.substring(
              7,
              numbers.length > 11 ? 11 : numbers.length,
            );
          }
        }
      }

      phoneController.value = TextEditingValue(
        text: formatted,
        selection: TextSelection.collapsed(offset: formatted.length),
      );
    }

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Nova Barbearia'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameController,
                decoration: const InputDecoration(
                  labelText: 'Nome do Proprietário *',
                  prefixIcon: Icon(Icons.person),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: passwordController,
                decoration: const InputDecoration(
                  labelText: 'Senha *',
                  prefixIcon: Icon(Icons.lock),
                  helperText: 'O usuário será gerado automaticamente',
                ),
                obscureText: true,
              ),
              const SizedBox(height: 24),
              const Divider(),
              const SizedBox(height: 24),
              TextField(
                controller: barbershopNameController,
                decoration: const InputDecoration(
                  labelText: 'Nome da Barbearia *',
                  prefixIcon: Icon(Icons.store),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: phoneController,
                decoration: const InputDecoration(
                  labelText: 'Telefone da Barbearia *',
                  prefixIcon: Icon(Icons.phone),
                  hintText: '(11) 99999-9999',
                ),
                keyboardType: TextInputType.phone,
                onChanged: formatPhone,
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: _isCreatingBarbershop
                ? null
                : () async {
                    if (nameController.text.isEmpty ||
                        passwordController.text.isEmpty ||
                        barbershopNameController.text.isEmpty ||
                        phoneController.text.isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Preencha todos os campos'),
                        ),
                      );
                      return;
                    }
                    setState(() => _isCreatingBarbershop = true);
                    try {
                      final response = await _api.createUser({
                        'name': nameController.text,
                        'password': passwordController.text,
                        'barbershopName': barbershopNameController.text,
                        'barbershopPhone': phoneController.text.replaceAll(
                          RegExp(r'[^0-9]'),
                          '',
                        ),
                      });
                      if (context.mounted) {
                        Navigator.pop(context);
                        final username =
                            response['username'] ?? 'usuário criado';
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              'Barbearia criada! Usuário: $username',
                            ),
                            backgroundColor: AppColors.success,
                            duration: const Duration(seconds: 5),
                          ),
                        );
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Erro: ${e.toString()}'),
                            backgroundColor: AppColors.error,
                          ),
                        );
                      }
                    } finally {
                      if (mounted)
                        setState(() => _isCreatingBarbershop = false);
                    }
                  },
            child: _isCreatingBarbershop
                ? const SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Criar'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Painel Administrativo'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadStats),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Logout',
            onPressed: () => _logout(context),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ResponsiveLayout(
              mobile: _buildContent(),
              desktop: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 1200),
                  child: _buildContent(),
                ),
              ),
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showCreateBarbershopDialog,
        icon: const Icon(Icons.add_business),
        label: const Text('Nova Barbearia'),
      ),
    );
  }

  Widget _buildContent() {
    return RefreshIndicator(
      onRefresh: _loadStats,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          // Welcome Header
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              gradient: AppColors.goldGradient,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Painel Administrativo',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: AppColors.black,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Gerencie todas as barbearias da plataforma',
                  style: TextStyle(
                    fontSize: 16,
                    color: AppColors.black.withOpacity(0.7),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 32),

          // Stats Grid
          LayoutBuilder(
            builder: (context, constraints) {
              final isWide = constraints.maxWidth > 600;
              return Wrap(
                spacing: 16,
                runSpacing: 16,
                children: [
                  SizedBox(
                    width: isWide
                        ? (constraints.maxWidth - 32) / 3
                        : constraints.maxWidth,
                    child: StatCard(
                      title: 'Total de Barbearias',
                      value: '${_stats['totalBarbershops'] ?? 0}',
                      icon: Icons.store,
                      color: AppColors.primaryGold,
                    ),
                  ),
                  SizedBox(
                    width: isWide
                        ? (constraints.maxWidth - 32) / 3
                        : constraints.maxWidth,
                    child: StatCard(
                      title: 'Usuários Ativos',
                      value: '${_stats['activeUsers'] ?? 0}',
                      icon: Icons.people,
                      color: AppColors.info,
                    ),
                  ),
                  SizedBox(
                    width: isWide
                        ? (constraints.maxWidth - 32) / 3
                        : constraints.maxWidth,
                    child: StatCard(
                      title: 'Receita Total',
                      value: 'R\$ ${_stats['totalRevenue'] ?? '0,00'}',
                      icon: Icons.attach_money,
                      color: AppColors.success,
                    ),
                  ),
                ],
              );
            },
          ),
          const SizedBox(height: 32),

          // Management Cards
          Card(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.primaryGold.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(
                          Icons.business_center,
                          color: AppColors.primaryGold,
                          size: 32,
                        ),
                      ),
                      const SizedBox(width: 16),
                      const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Gerenciamento de Barbearias',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'Crie e gerencie barbearias na plataforma',
                            style: TextStyle(color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  const Text(
                    'Recursos Administrativos:',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  _buildFeatureItem('Criar novas barbearias com proprietários'),
                  _buildFeatureItem('Gerenciar usuários e permissões'),
                  _buildFeatureItem('Visualizar estatísticas da plataforma'),
                  _buildFeatureItem('Monitorar desempenho das barbearias'),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFeatureItem(String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          const Icon(Icons.check_circle, color: AppColors.success, size: 20),
          const SizedBox(width: 12),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}
