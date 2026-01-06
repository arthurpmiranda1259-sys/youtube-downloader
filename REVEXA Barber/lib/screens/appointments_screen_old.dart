import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../theme/app_colors.dart';
import '../services/api_service.dart';
import '../models/appointment.dart';
import '../models/client.dart';
import '../models/service.dart';
import '../models/barber.dart';

class AppointmentsScreen extends StatefulWidget {
  const AppointmentsScreen({super.key});

  @override
  State<AppointmentsScreen> createState() => _AppointmentsScreenState();
}

class _AppointmentsScreenState extends State<AppointmentsScreen> {
  final ApiService _api = ApiService();
  List<Appointment> _appointments = [];
  List<Client> _clients = [];
  List<Service> _services = [];
  List<Barber> _barbers = [];
  bool _isLoading = true;
  DateTime _selectedDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final dateStr =
          '${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}';

      final appointments = await _api.getAppointments(dateStr);
      final clients = await _api.getClients();
      final services = await _api.getServices();
      final barbers = await _api.getBarbers();

      setState(() {
        _appointments = appointments
            .map((json) => Appointment.fromJson(json))
            .toList();
        _clients = clients.map((json) => Client.fromJson(json)).toList();
        _services = services.map((json) => Service.fromJson(json)).toList();
        _barbers = barbers.map((json) => Barber.fromJson(json)).toList();
        _isLoading = false;
        print(
          'Loaded: ${_clients.length} clients, ${_services.length} services, ${_barbers.length} barbers',
        );
      });
    } catch (e) {
      setState(() => _isLoading = false);
      print('Error loading data: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erro ao carregar dados: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _selectDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
    );

    if (date != null) {
      setState(() => _selectedDate = date);
      _loadData();
    }
  }

  Future<void> _updateStatus(String id, String status) async {
    try {
      await _api.updateAppointmentStatus(id, status);
      _loadData();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Status atualizado para: $status'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Erro ao atualizar status'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _completeAppointment(Appointment appointment) async {
    String? paymentMethod = 'cash';

    await showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Finalizar Agendamento'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Selecione a forma de pagamento:'),
              const SizedBox(height: 16),
              RadioListTile<String>(
                title: const Text('Dinheiro'),
                value: 'cash',
                groupValue: paymentMethod,
                onChanged: (value) =>
                    setDialogState(() => paymentMethod = value),
              ),
              RadioListTile<String>(
                title: const Text('Cartão'),
                value: 'card',
                groupValue: paymentMethod,
                onChanged: (value) =>
                    setDialogState(() => paymentMethod = value),
              ),
              RadioListTile<String>(
                title: const Text('PIX'),
                value: 'pix',
                groupValue: paymentMethod,
                onChanged: (value) =>
                    setDialogState(() => paymentMethod = value),
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
                  final service = _services.firstWhere(
                    (s) => s.id == appointment.serviceId,
                  );
                  await _api.createPayment({
                    'appointmentId': appointment.id,
                    'amount': service.price,
                    'paymentMethod': paymentMethod,
                  });
                  _loadData();
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Agendamento finalizado com sucesso!'),
                        backgroundColor: AppColors.success,
                      ),
                    );
                  }
                } catch (e) {
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Erro ao finalizar agendamento'),
                        backgroundColor: AppColors.error,
                      ),
                    );
                  }
                }
              },
              child: const Text('Confirmar'),
            ),
          ],
        ),
      ),
    );
  }

  void _showAddAppointmentDialog() {
    // Check if we have data
    if (_clients.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Cadastre pelo menos um cliente primeiro'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }
    if (_services.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Cadastre pelo menos um serviço primeiro'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }
    if (_barbers.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Cadastre pelo menos um barbeiro primeiro'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    Client? selectedClient;
    Service? selectedService;
    Barber? selectedBarber;
    DateTime selectedDate = _selectedDate;
    TimeOfDay selectedTime = TimeOfDay.now();

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Novo Agendamento'),
          content: SizedBox(
            width: 500,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  DropdownButtonFormField<Client>(
                    decoration: const InputDecoration(
                      labelText: 'Cliente *',
                      border: OutlineInputBorder(),
                    ),
                    initialValue: selectedClient,
                    isExpanded: true,
                    items: _clients.map((client) {
                      return DropdownMenuItem(
                        value: client,
                        child: Text(client.name),
                      );
                    }).toList(),
                    onChanged: (client) {
                      setDialogState(() => selectedClient = client);
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<Service>(
                    decoration: const InputDecoration(
                      labelText: 'Serviço *',
                      border: OutlineInputBorder(),
                    ),
                    initialValue: selectedService,
                    isExpanded: true,
                    items: _services.map((service) {
                      return DropdownMenuItem(
                        value: service,
                        child: Text(
                          '${service.name} - ${service.formattedPrice}',
                        ),
                      );
                    }).toList(),
                    onChanged: (service) {
                      setDialogState(() => selectedService = service);
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<Barber>(
                    decoration: const InputDecoration(
                      labelText: 'Barbeiro *',
                      border: OutlineInputBorder(),
                    ),
                    initialValue: selectedBarber,
                    isExpanded: true,
                    items: _barbers.map((barber) {
                      return DropdownMenuItem(
                        value: barber,
                        child: Text(barber.name),
                      );
                    }).toList(),
                    onChanged: (barber) {
                      setDialogState(() => selectedBarber = barber);
                    },
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: selectedDate,
                        firstDate: DateTime.now(),
                        lastDate: DateTime(2030),
                      );
                      if (date != null) {
                        setDialogState(() => selectedDate = date);
                      }
                    },
                    child: InputDecorator(
                      decoration: const InputDecoration(
                        labelText: 'Data',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.calendar_today),
                      ),
                      child: Text(
                        DateFormat('dd/MM/yyyy').format(selectedDate),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final time = await showTimePicker(
                        context: context,
                        initialTime: selectedTime,
                      );
                      if (time != null) {
                        setDialogState(() => selectedTime = time);
                      }
                    },
                    child: InputDecorator(
                      decoration: const InputDecoration(
                        labelText: 'Horário',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.access_time),
                      ),
                      child: Text(selectedTime.format(context)),
                    ),
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: () async {
                if (selectedClient == null ||
                    selectedService == null ||
                    selectedBarber == null) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Preencha todos os campos')),
                  );
                  return;
                }

                final dateTime = DateTime(
                  selectedDate.year,
                  selectedDate.month,
                  selectedDate.day,
                  selectedTime.hour,
                  selectedTime.minute,
                );

                final dateTimeStr =
                    '${dateTime.year}-${dateTime.month.toString().padLeft(2, '0')}-${dateTime.day.toString().padLeft(2, '0')} ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}:00';

                try {
                  final payload = {
                    'clientId': selectedClient!.id,
                    'serviceId': selectedService!.id,
                    'barberId': selectedBarber!.id,
                    'dateTime': dateTimeStr,
                  };

                  print('Creating appointment with data: $payload');

                  await _api.createAppointment(payload);

                  if (context.mounted) {
                    Navigator.pop(context);
                    _loadData();
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Agendamento criado com sucesso!'),
                        backgroundColor: AppColors.success,
                      ),
                    );
                  }
                } catch (e) {
                  print('Error creating appointment: $e');
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Erro ao criar agendamento: $e'),
                        backgroundColor: AppColors.error,
                      ),
                    );
                  }
                }
              },
              child: const Text('Criar'),
            ),
          ],
        ),
      ),
    );
  }

  String _getClientName(String clientId) {
    try {
      return _clients.firstWhere((c) => c.id == clientId).name;
    } catch (e) {
      return 'Cliente #$clientId';
    }
  }

  String _getServiceName(String serviceId) {
    try {
      return _services.firstWhere((s) => s.id == serviceId).name;
    } catch (e) {
      return 'Serviço #$serviceId';
    }
  }

  String _getBarberName(String barberId) {
    try {
      return _barbers.firstWhere((b) => b.id == barberId).name;
    } catch (e) {
      return 'Barbeiro #$barberId';
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return AppColors.warning;
      case 'in_progress':
        return AppColors.info;
      case 'completed':
        return AppColors.success;
      case 'cancelled':
        return AppColors.error;
      default:
        return AppColors.textSecondary;
    }
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'pending':
        return 'Pendente';
      case 'in_progress':
        return 'Em Atendimento';
      case 'completed':
        return 'Concluído';
      case 'cancelled':
        return 'Cancelado';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Agendamentos'),
        actions: [
          IconButton(
            icon: const Icon(Icons.calendar_today),
            onPressed: _selectDate,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Date selector
                Container(
                  padding: const EdgeInsets.all(16),
                  color: AppColors.surfaceLight,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        DateFormat('dd/MM/yyyy').format(_selectedDate),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        '${_appointments.length} agendamentos',
                        style: const TextStyle(color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: _appointments.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.event_busy,
                                size: 64,
                                color: AppColors.textSecondary.withOpacity(0.5),
                              ),
                              const SizedBox(height: 16),
                              const Text(
                                'Nenhum agendamento para este dia',
                                style: TextStyle(
                                  color: AppColors.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _loadData,
                          child: ListView.builder(
                            padding: const EdgeInsets.all(16),
                            itemCount: _appointments.length,
                            itemBuilder: (context, index) {
                              final appointment = _appointments[index];
                              final time = DateFormat(
                                'HH:mm',
                              ).format(appointment.dateTime);

                              return Card(
                                margin: const EdgeInsets.only(bottom: 12),
                                child: ExpansionTile(
                                  leading: Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: _getStatusColor(
                                        appointment.status,
                                      ).withOpacity(0.2),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          time.split(':')[0],
                                          style: TextStyle(
                                            fontSize: 20,
                                            fontWeight: FontWeight.bold,
                                            color: _getStatusColor(
                                              appointment.status,
                                            ),
                                          ),
                                        ),
                                        Text(
                                          time.split(':')[1],
                                          style: TextStyle(
                                            fontSize: 14,
                                            color: _getStatusColor(
                                              appointment.status,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  title: Text(
                                    _getClientName(appointment.clientId),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  subtitle: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      const SizedBox(height: 4),
                                      Text(
                                        '${_getServiceName(appointment.serviceId)} • ${_getBarberName(appointment.barberId)}',
                                      ),
                                      const SizedBox(height: 4),
                                      Chip(
                                        label: Text(
                                          _getStatusText(appointment.status),
                                          style: const TextStyle(fontSize: 11),
                                        ),
                                        backgroundColor: _getStatusColor(
                                          appointment.status,
                                        ),
                                        padding: const EdgeInsets.all(0),
                                        materialTapTargetSize:
                                            MaterialTapTargetSize.shrinkWrap,
                                      ),
                                    ],
                                  ),
                                  children: [
                                    Padding(
                                      padding: const EdgeInsets.all(16),
                                      child: Column(
                                        children: [
                                          if (appointment.status ==
                                              'pending') ...[
                                            Row(
                                              children: [
                                                Expanded(
                                                  child: ElevatedButton.icon(
                                                    onPressed: () =>
                                                        _updateStatus(
                                                          appointment.id,
                                                          'in_progress',
                                                        ),
                                                    icon: const Icon(
                                                      Icons.play_arrow,
                                                    ),
                                                    label: const Text(
                                                      'Iniciar',
                                                    ),
                                                    style:
                                                        ElevatedButton.styleFrom(
                                                          backgroundColor:
                                                              AppColors.info,
                                                          foregroundColor:
                                                              Colors.white,
                                                        ),
                                                  ),
                                                ),
                                                const SizedBox(width: 8),
                                                Expanded(
                                                  child: ElevatedButton.icon(
                                                    onPressed: () =>
                                                        _updateStatus(
                                                          appointment.id,
                                                          'cancelled',
                                                        ),
                                                    icon: const Icon(
                                                      Icons.close,
                                                    ),
                                                    label: const Text(
                                                      'Cancelar',
                                                    ),
                                                    style:
                                                        ElevatedButton.styleFrom(
                                                          backgroundColor:
                                                              AppColors.error,
                                                          foregroundColor:
                                                              Colors.white,
                                                        ),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ],
                                          if (appointment.status ==
                                              'in_progress') ...[
                                            ElevatedButton.icon(
                                              onPressed: () =>
                                                  _completeAppointment(
                                                    appointment,
                                                  ),
                                              icon: const Icon(
                                                Icons.check_circle,
                                              ),
                                              label: const Text(
                                                'Finalizar Atendimento',
                                              ),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor:
                                                    AppColors.success,
                                                foregroundColor: Colors.white,
                                                minimumSize:
                                                    const Size.fromHeight(40),
                                              ),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                        ),
                ),
              ],
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddAppointmentDialog,
        icon: const Icon(Icons.add),
        label: const Text('Novo Agendamento'),
      ),
    );
  }
}
