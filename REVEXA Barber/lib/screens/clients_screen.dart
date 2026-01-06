import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../services/api_service.dart';
import '../models/client.dart';
import 'package:intl/intl.dart';
import 'client_history_screen.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/success_animation.dart';

class ClientsScreen extends StatefulWidget {
  const ClientsScreen({super.key});

  @override
  State<ClientsScreen> createState() => _ClientsScreenState();
}

class _ClientsScreenState extends State<ClientsScreen> {
  final ApiService _api = ApiService();
  List<Client> _clients = [];
  List<Client> _filteredClients = [];
  bool _isLoading = true;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadClients();
    _searchController.addListener(_filterClients);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _filterClients() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      _filteredClients = _clients.where((client) {
        return client.name.toLowerCase().contains(query) ||
            (client.phone?.toLowerCase().contains(query) ?? false) ||
            (client.email?.toLowerCase().contains(query) ?? false);
      }).toList();
    });
  }

  Future<void> _loadClients() async {
    setState(() => _isLoading = true);
    try {
      final data = await _api.getClients();
      setState(() {
        _clients = data.map((json) => Client.fromJson(json)).toList();
        _filteredClients = _clients;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  void _showAddClientDialog() {
    final nameController = TextEditingController();
    final phoneController = TextEditingController();
    final notesController = TextEditingController();
    DateTime? birthDate;

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
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Novo Cliente'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameController,
                  decoration: const InputDecoration(
                    labelText: 'Nome Completo *',
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: phoneController,
                  decoration: const InputDecoration(
                    labelText: 'Telefone *',
                    hintText: '(11) 99999-9999',
                  ),
                  keyboardType: TextInputType.phone,
                  onChanged: formatPhone,
                ),
                const SizedBox(height: 16),
                ListTile(
                  title: Text(
                    birthDate != null
                        ? DateFormat('dd/MM/yyyy').format(birthDate!)
                        : 'Data de Nascimento',
                  ),
                  trailing: const Icon(Icons.calendar_today),
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: DateTime.now(),
                      firstDate: DateTime(1900),
                      lastDate: DateTime.now(),
                    );
                    if (date != null) {
                      setDialogState(() => birthDate = date);
                    }
                  },
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: notesController,
                  decoration: const InputDecoration(labelText: 'Observações'),
                  maxLines: 3,
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
                    phoneController.text.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Preencha os campos obrigatórios'),
                    ),
                  );
                  return;
                }

                try {
                  await _api.createClient({
                    'name': nameController.text,
                    'phone': phoneController.text.replaceAll(
                      RegExp(r'[^0-9]'),
                      '',
                    ),
                    'birthDate': birthDate?.toIso8601String().split('T')[0],
                    'notes': notesController.text,
                  });

                  if (context.mounted) {
                    Navigator.pop(context);
                    showSuccessDialog(context, 'Cliente cadastrado!');
                    _loadClients();
                  }
                } catch (e) {
                  if (context.mounted) {
                    showSimpleError(
                      context,
                      'Não foi possível cadastrar. Tente novamente.',
                    );
                  }
                }
              },
              child: const Text('Salvar'),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditClientDialog(Client client) {
    final nameController = TextEditingController(text: client.name);
    final phoneController = TextEditingController(text: client.formattedPhone);
    final notesController = TextEditingController(text: client.notes);
    DateTime? birthDate = client.birthDate;

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
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Editar Cliente'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameController,
                  decoration: const InputDecoration(
                    labelText: 'Nome Completo *',
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: phoneController,
                  decoration: const InputDecoration(
                    labelText: 'Telefone *',
                    hintText: '(11) 99999-9999',
                  ),
                  keyboardType: TextInputType.phone,
                  onChanged: formatPhone,
                ),
                const SizedBox(height: 16),
                ListTile(
                  title: Text(
                    birthDate != null
                        ? DateFormat('dd/MM/yyyy').format(birthDate!)
                        : 'Data de Nascimento',
                  ),
                  trailing: const Icon(Icons.calendar_today),
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: birthDate ?? DateTime.now(),
                      firstDate: DateTime(1900),
                      lastDate: DateTime.now(),
                    );
                    if (date != null) {
                      setDialogState(() => birthDate = date);
                    }
                  },
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: notesController,
                  decoration: const InputDecoration(labelText: 'Observações'),
                  maxLines: 3,
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
                    phoneController.text.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Preencha os campos obrigatórios'),
                    ),
                  );
                  return;
                }

                try {
                  await _api.updateClient(client.id, {
                    'name': nameController.text,
                    'phone': phoneController.text.replaceAll(
                      RegExp(r'[^0-9]'),
                      '',
                    ),
                    'birthDate': birthDate?.toIso8601String().split('T')[0],
                    'notes': notesController.text,
                  });

                  if (context.mounted) {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Cliente atualizado com sucesso!'),
                        backgroundColor: AppColors.success,
                      ),
                    );
                    _loadClients();
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Erro ao atualizar cliente: $e'),
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
      ),
    );
  }

  void _showDeleteClientDialog(Client client) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Excluir Cliente'),
        content: Text(
          'Tem certeza que deseja excluir ${client.name}?\n\nEsta ação não pode ser desfeita.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () async {
              try {
                await _api.deleteClient(client.id);

                if (context.mounted) {
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Cliente excluído com sucesso!'),
                      backgroundColor: AppColors.success,
                    ),
                  );
                  _loadClients();
                }
              } catch (e) {
                if (context.mounted) {
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Erro ao excluir cliente: $e'),
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
          // Barra de pesquisa
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Buscar por nome, telefone ou email...',
                prefixIcon: const Icon(
                  Icons.search,
                  color: AppColors.primaryGold,
                ),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                        },
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.textSecondary),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.primaryGold),
                ),
              ),
            ),
          ),
          // Lista de clientes
          Expanded(
            child: _isLoading
                ? const SkeletonList()
                : RefreshIndicator(
                    onRefresh: _loadClients,
                    child: _filteredClients.isEmpty
                        ? Center(
                            child: Text(
                              _clients.isEmpty
                                  ? 'Nenhum cliente cadastrado'
                                  : 'Nenhum cliente encontrado',
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
                            itemCount: _filteredClients.length,
                            itemBuilder: (context, index) {
                              final client = _filteredClients[index];
                              return Card(
                                margin: const EdgeInsets.only(bottom: 12),
                                child: ListTile(
                                  onTap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) =>
                                            ClientHistoryScreen(client: client),
                                      ),
                                    );
                                  },
                                  leading: CircleAvatar(
                                    backgroundColor: AppColors.primaryGold,
                                    child: Text(
                                      client.name[0].toUpperCase(),
                                      style: const TextStyle(
                                        color: AppColors.black,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                  title: Text(client.name),
                                  subtitle: Text(client.formattedPhone),
                                  trailing: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      if (client.hasBirthdayToday)
                                        const Padding(
                                          padding: EdgeInsets.only(right: 8),
                                          child: Icon(
                                            Icons.cake,
                                            color: AppColors.warning,
                                          ),
                                        ),
                                      PopupMenuButton<String>(
                                        icon: const Icon(Icons.more_vert),
                                        onSelected: (value) {
                                          if (value == 'history') {
                                            Navigator.push(
                                              context,
                                              MaterialPageRoute(
                                                builder: (context) =>
                                                    ClientHistoryScreen(
                                                      client: client,
                                                    ),
                                              ),
                                            );
                                          } else if (value == 'edit') {
                                            _showEditClientDialog(client);
                                          } else if (value == 'delete') {
                                            _showDeleteClientDialog(client);
                                          }
                                        },
                                        itemBuilder: (context) => [
                                          const PopupMenuItem(
                                            value: 'history',
                                            child: Row(
                                              children: [
                                                Icon(Icons.history, size: 20),
                                                SizedBox(width: 12),
                                                Text('Ver Histórico'),
                                              ],
                                            ),
                                          ),
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
        onPressed: _showAddClientDialog,
        icon: const Icon(Icons.add),
        label: const Text('Novo Cliente'),
      ),
    );
  }
}
