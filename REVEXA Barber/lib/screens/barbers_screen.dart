import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../services/api_service.dart';
import '../models/barber.dart';
import 'barber_availability_screen.dart';

class BarbersScreen extends StatefulWidget {
  const BarbersScreen({super.key});

  @override
  State<BarbersScreen> createState() => _BarbersScreenState();
}

class _BarbersScreenState extends State<BarbersScreen> {
  final ApiService _api = ApiService();
  List<Barber> _barbers = [];
  List<Barber> _filteredBarbers = [];
  bool _isLoading = true;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadBarbers();
    _searchController.addListener(_filterBarbers);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _filterBarbers() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      _filteredBarbers = _barbers.where((barber) {
        return barber.name.toLowerCase().contains(query) ||
            (barber.phone.toLowerCase().contains(query));
      }).toList();
    });
  }

  Future<void> _loadBarbers() async {
    setState(() => _isLoading = true);
    try {
      final data = await _api.getBarbers();
      setState(() {
        _barbers = data.map((json) => Barber.fromJson(json)).toList();
        _filteredBarbers = _barbers;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  void _showBarberDialog({Barber? barber}) {
    final isEditing = barber != null;
    final nameController = TextEditingController(text: barber?.name);
    final phoneController = TextEditingController(text: barber?.formattedPhone);
    final commissionController = TextEditingController(
      text: barber?.commissionPercentage.toStringAsFixed(0) ?? '50',
    );
    final pixKeyController = TextEditingController(text: barber?.pixKey);

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isEditing ? 'Editar Barbeiro' : 'Novo Barbeiro'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameController,
                decoration: const InputDecoration(
                  labelText: 'Nome Completo *',
                  prefixIcon: Icon(Icons.person),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: phoneController,
                decoration: const InputDecoration(
                  labelText: 'Telefone *',
                  prefixIcon: Icon(Icons.phone),
                ),
                keyboardType: TextInputType.phone,
              ),
              const SizedBox(height: 16),
              TextField(
                controller: commissionController,
                decoration: const InputDecoration(
                  labelText: 'Comissão (%) *',
                  prefixIcon: Icon(Icons.percent),
                ),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 16),
              TextField(
                controller: pixKeyController,
                decoration: const InputDecoration(
                  labelText: 'Chave PIX',
                  prefixIcon: Icon(Icons.vpn_key),
                ),
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
            onPressed: () async {
              if (nameController.text.isEmpty ||
                  phoneController.text.isEmpty ||
                  commissionController.text.isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Preencha os campos obrigatórios'),
                  ),
                );
                return;
              }

              final Map<String, dynamic> barberData = {
                'name': nameController.text,
                'phone': phoneController.text.replaceAll(RegExp(r'[^0-9]'), ''),
                'commission_percentage':
                    double.tryParse(commissionController.text) ?? 50.0,
                'pix_key': pixKeyController.text,
                // campo removido
              };

              try {
                if (isEditing) {
                  await _api.updateBarber(barber.id, barberData);
                } else {
                  await _api.createBarber(barberData);
                }

                if (context.mounted) {
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                        'Barbeiro ${isEditing ? 'atualizado' : 'cadastrado'}!',
                      ),
                      backgroundColor: AppColors.success,
                    ),
                  );
                  _loadBarbers();
                }
              } catch (e) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Erro: $e'),
                      backgroundColor: AppColors.error,
                    ),
                  );
                }
              }
            },
            child: const Text('Salvar'),
          ),
        ],
      ),
    );
  }

  void _showDeleteBarberDialog(Barber barber) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Excluir Barbeiro'),
        content: Text('Tem certeza que deseja excluir ${barber.name}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () async {
              try {
                await _api.deleteBarber(barber.id);
                if (context.mounted) {
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Barbeiro excluído!'),
                      backgroundColor: AppColors.success,
                    ),
                  );
                  _loadBarbers();
                }
              } catch (e) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Erro: $e'),
                      backgroundColor: AppColors.error,
                    ),
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Excluir'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Buscar barbeiros...',
                prefixIcon: const Icon(
                  Icons.search,
                  color: AppColors.primaryGold,
                ),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () => _searchController.clear(),
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.primaryGold),
                ),
              ),
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _loadBarbers,
                    child: _filteredBarbers.isEmpty
                        ? Center(
                            child: Text(
                              _barbers.isEmpty
                                  ? 'Nenhum barbeiro cadastrado'
                                  : 'Nenhum barbeiro encontrado',
                              style: const TextStyle(
                                color: AppColors.textSecondary,
                              ),
                            ),
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.only(
                              left: 16,
                              right: 16,
                              bottom: 16,
                            ),
                            itemCount: _filteredBarbers.length,
                            itemBuilder: (context, index) {
                              final barber = _filteredBarbers[index];
                              return Card(
                                margin: const EdgeInsets.only(bottom: 12),
                                child: ListTile(
                                  leading: CircleAvatar(
                                    backgroundColor: AppColors.primaryGold,
                                    child: Text(
                                      barber.name[0].toUpperCase(),
                                      style: const TextStyle(
                                        color: AppColors.black,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                  title: Text(barber.name),
                                  subtitle: Text(barber.formattedPhone),
                                  trailing: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 12,
                                          vertical: 6,
                                        ),
                                        decoration: BoxDecoration(
                                          color: AppColors.success.withOpacity(
                                            0.2,
                                          ),
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                        ),
                                        child: Text(
                                          barber.formattedCommission,
                                          style: const TextStyle(
                                            color: AppColors.success,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                      PopupMenuButton<String>(
                                        icon: const Icon(Icons.more_vert),
                                        onSelected: (value) {
                                          if (value == 'edit') {
                                            _showBarberDialog(barber: barber);
                                          } else if (value == 'availability') {
                                            Navigator.push(
                                              context,
                                              MaterialPageRoute(
                                                builder: (_) =>
                                                    BarberAvailabilityScreen(
                                                      barberId: barber.id,
                                                      barberName: barber.name,
                                                    ),
                                              ),
                                            );
                                          } else if (value == 'delete') {
                                            _showDeleteBarberDialog(barber);
                                          }
                                        },
                                        itemBuilder: (context) => [
                                          const PopupMenuItem(
                                            value: 'edit',
                                            child: Row(
                                              children: [
                                                Icon(Icons.edit, size: 20),
                                                SizedBox(width: 12),
                                                Text('Editar'),
                                              ],
                                            ),
                                          ),
                                          const PopupMenuItem(
                                            value: 'availability',
                                            child: Row(
                                              children: [
                                                Icon(
                                                  Icons.calendar_today,
                                                  size: 20,
                                                ),
                                                SizedBox(width: 12),
                                                Text('Disponibilidade'),
                                              ],
                                            ),
                                          ),
                                          const PopupMenuItem(
                                            value: 'delete',
                                            child: Row(
                                              children: [
                                                Icon(
                                                  Icons.delete,
                                                  size: 20,
                                                  color: AppColors.error,
                                                ),
                                                SizedBox(width: 12),
                                                Text(
                                                  'Excluir',
                                                  style: TextStyle(
                                                    color: AppColors.error,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                  ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showBarberDialog(),
        icon: const Icon(Icons.add),
        label: const Text('Novo Barbeiro'),
      ),
    );
  }
}
