import 'package:flutter/material.dart';
import 'package:table_calendar/table_calendar.dart';
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
  DateTime _selectedDay = DateTime.now();
  DateTime _focusedDay = DateTime.now();
  List<Appointment> _appointments = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadAppointments();
  }

  Future<void> _loadAppointments() async {
    setState(() => _isLoading = true);
    try {
      final dateStr =
          '${_selectedDay.year}-${_selectedDay.month.toString().padLeft(2, '0')}-${_selectedDay.day.toString().padLeft(2, '0')}';
      final data = await _api.getAppointments(dateStr);
      setState(() {
        _appointments = (data)
            .map((json) => Appointment.fromJson(json))
            .toList();
        _appointments.sort((a, b) => a.dateTime.compareTo(b.dateTime));
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _markAsDone(Appointment appointment) async {
    if (appointment.servicePrice == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Serviço sem preço definido'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    try {
      // Update appointment status
      await _api.updateAppointmentStatus(appointment.id, 'completed');

      // Create payment record
      await _api.createPayment({
        'appointmentId': appointment.id,
        'amount': appointment.servicePrice,
        'paymentMethod': 'cash', // Default to cash
        'paymentDate': DateTime.now().toIso8601String(),
      });

      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('✅ Agendamento concluído e pagamento registrado!'),
            backgroundColor: AppColors.success,
          ),
        );
        _loadAppointments();
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erro: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _cancelAppointment(Appointment appointment) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancelar Agendamento'),
        content: Text(
          'Tem certeza que deseja cancelar o agendamento de ${appointment.clientName ?? 'Cliente'}?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Não'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Sim, Cancelar'),
          ),
        ],
      ),
    );

    if (confirm == true && context.mounted) {
      try {
        await _api.updateAppointmentStatus(appointment.id, 'cancelled');
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Agendamento cancelado'),
            backgroundColor: AppColors.warning,
          ),
        );
        _loadAppointments();
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erro ao cancelar: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _showEditDialog(Appointment appointment) async {
    final clients = await _api.getClients();
    final services = await _api.getServices();
    final barbers = await _api.getBarbers();

    if (!mounted) return;

    Client? selectedClient = clients
        .map((json) => Client.fromJson(json))
        .firstWhere(
          (c) => c.id == appointment.clientId,
          orElse: () => Client.fromJson(clients.first),
        );
    Service? selectedService = services
        .map((json) => Service.fromJson(json))
        .firstWhere(
          (s) => s.id == appointment.serviceId,
          orElse: () => Service.fromJson(services.first),
        );
    Barber? selectedBarber = barbers
        .map((json) => Barber.fromJson(json))
        .firstWhere(
          (b) => b.id == appointment.barberId,
          orElse: () => Barber.fromJson(barbers.first),
        );

    DateTime selectedDate = appointment.dateTime;
    TimeOfDay selectedTime = TimeOfDay(
      hour: appointment.dateTime.hour,
      minute: appointment.dateTime.minute,
    );

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Editar Agendamento',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<Client>(
                  value: selectedClient,
                  isExpanded: true,
                  decoration: const InputDecoration(labelText: 'Cliente'),
                  items: clients
                      .map((json) => Client.fromJson(json))
                      .map(
                        (client) => DropdownMenuItem(
                          value: client,
                          child: Text(client.name),
                        ),
                      )
                      .toList(),
                  onChanged: (value) =>
                      setDialogState(() => selectedClient = value),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<Service>(
                  value: selectedService,
                  isExpanded: true,
                  decoration: const InputDecoration(labelText: 'Serviço'),
                  items: services
                      .map((json) => Service.fromJson(json))
                      .map(
                        (service) => DropdownMenuItem(
                          value: service,
                          child: Text(service.name),
                        ),
                      )
                      .toList(),
                  onChanged: (value) =>
                      setDialogState(() => selectedService = value),
                ),
                const SizedBox(height: 16),
                if (barbers.isNotEmpty)
                  DropdownButtonFormField<Barber?>(
                    value: selectedBarber,
                    isExpanded: true,
                    decoration: const InputDecoration(labelText: 'Barbeiro'),
                    items: [
                      const DropdownMenuItem<Barber?>(
                        value: null,
                        child: Text('Nenhum'),
                      ),
                      ...barbers
                          .map((json) => Barber.fromJson(json))
                          .map(
                            (barber) => DropdownMenuItem(
                              value: barber,
                              child: Text(barber.name),
                            ),
                          ),
                    ],
                    onChanged: (value) =>
                        setDialogState(() => selectedBarber = value),
                  ),
                const SizedBox(height: 16),
                ListTile(
                  title: Text(DateFormat('dd/MM/yyyy').format(selectedDate)),
                  leading: const Icon(Icons.calendar_today),
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: selectedDate,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365)),
                    );
                    if (date != null) {
                      setDialogState(() => selectedDate = date);
                    }
                  },
                ),
                ListTile(
                  title: Text(selectedTime.format(context)),
                  leading: const Icon(Icons.access_time),
                  onTap: () async {
                    final time = await showTimePicker(
                      context: context,
                      initialTime: selectedTime,
                    );
                    if (time != null) {
                      setDialogState(() => selectedTime = time);
                    }
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancelar'),
                    ),
                    ElevatedButton(
                      onPressed: () async {
                        if (selectedClient == null || selectedService == null) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Selecione cliente e serviço'),
                            ),
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

                        try {
                          await _api.updateAppointment(appointment.id, {
                            'clientId': selectedClient!.id,
                            'serviceId': selectedService!.id,
                            'barberId': selectedBarber?.id,
                            'dateTime': dateTime.toIso8601String(),
                          });

                          if (context.mounted) {
                            Navigator.pop(context);
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Agendamento atualizado!'),
                                backgroundColor: AppColors.success,
                              ),
                            );
                            _loadAppointments();
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
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showAddAppointmentDialog() async {
    final clients = await _api.getClients();
    final services = await _api.getServices();
    final barbers = await _api.getBarbers();

    if (!mounted) return;

    Client? selectedClient;
    Service? selectedService;
    Barber? selectedBarber;
    DateTime selectedDate = _selectedDay;
    TimeOfDay selectedTime = TimeOfDay.now();

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Novo Agendamento',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<Client>(
                  value: selectedClient,
                  isExpanded: true,
                  decoration: const InputDecoration(labelText: 'Cliente *'),
                  items: clients
                      .map((json) => Client.fromJson(json))
                      .map(
                        (client) => DropdownMenuItem(
                          value: client,
                          child: Text(client.name),
                        ),
                      )
                      .toList(),
                  onChanged: (value) =>
                      setDialogState(() => selectedClient = value),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<Service>(
                  value: selectedService,
                  isExpanded: true,
                  decoration: const InputDecoration(labelText: 'Serviço *'),
                  items: services
                      .map((json) => Service.fromJson(json))
                      .map(
                        (service) => DropdownMenuItem(
                          value: service,
                          child: Text(service.name),
                        ),
                      )
                      .toList(),
                  onChanged: (value) =>
                      setDialogState(() => selectedService = value),
                ),
                const SizedBox(height: 16),
                if (barbers.isNotEmpty)
                  DropdownButtonFormField<Barber?>(
                    value: selectedBarber,
                    isExpanded: true,
                    decoration: const InputDecoration(labelText: 'Barbeiro'),
                    items: [
                      const DropdownMenuItem<Barber?>(
                        value: null,
                        child: Text('Nenhum'),
                      ),
                      ...barbers
                          .map((json) => Barber.fromJson(json))
                          .map(
                            (barber) => DropdownMenuItem(
                              value: barber,
                              child: Text(barber.name),
                            ),
                          ),
                    ],
                    onChanged: (value) =>
                        setDialogState(() => selectedBarber = value),
                  ),
                const SizedBox(height: 16),
                ListTile(
                  title: Text(DateFormat('dd/MM/yyyy').format(selectedDate)),
                  leading: const Icon(Icons.calendar_today),
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: selectedDate,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365)),
                    );
                    if (date != null) {
                      setDialogState(() => selectedDate = date);
                    }
                  },
                ),
                ListTile(
                  title: Text(selectedTime.format(context)),
                  leading: const Icon(Icons.access_time),
                  onTap: () async {
                    final time = await showTimePicker(
                      context: context,
                      initialTime: selectedTime,
                    );
                    if (time != null) {
                      setDialogState(() => selectedTime = time);
                    }
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancelar'),
                    ),
                    ElevatedButton(
                      onPressed: () async {
                        if (selectedClient == null || selectedService == null) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Selecione cliente e serviço'),
                            ),
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

                        try {
                          await _api.createAppointment({
                            'clientId': selectedClient!.id,
                            'serviceId': selectedService!.id,
                            'barberId': selectedBarber?.id,
                            'dateTime': dateTime.toIso8601String(),
                            'status': 'scheduled',
                          });

                          if (context.mounted) {
                            Navigator.pop(context);
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Agendamento criado!'),
                                backgroundColor: AppColors.success,
                              ),
                            );
                            _loadAppointments();
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
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          Card(
            margin: const EdgeInsets.all(16),
            child: TableCalendar(
              firstDay: DateTime.utc(2020, 1, 1),
              lastDay: DateTime.utc(2030, 12, 31),
              focusedDay: _focusedDay,
              selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
              locale: 'pt_BR',
              headerStyle: const HeaderStyle(
                formatButtonVisible: false,
                titleCentered: true,
              ),
              calendarStyle: CalendarStyle(
                selectedDecoration: BoxDecoration(
                  color: AppColors.primaryGold,
                  shape: BoxShape.circle,
                ),
                todayDecoration: BoxDecoration(
                  color: AppColors.primaryGold.withOpacity(0.5),
                  shape: BoxShape.circle,
                ),
              ),
              onDaySelected: (selectedDay, focusedDay) {
                setState(() {
                  _selectedDay = selectedDay;
                  _focusedDay = focusedDay;
                });
                _loadAppointments();
              },
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Agendamentos do Dia',
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  DateFormat('dd/MM/yyyy').format(_selectedDay),
                  style: const TextStyle(color: AppColors.textSecondary),
                ),
              ],
            ),
          ),
          const Divider(),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _appointments.isEmpty
                ? const Center(
                    child: Text(
                      'Nenhum agendamento para este dia',
                      style: TextStyle(color: AppColors.textSecondary),
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _appointments.length,
                    itemBuilder: (context, index) {
                      final apt = _appointments[index];
                      final isCompleted = apt.status == 'completed';
                      final isCancelled = apt.status == 'cancelled';

                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        color: isCancelled
                            ? AppColors.error.withOpacity(0.1)
                            : isCompleted
                            ? AppColors.success.withOpacity(0.1)
                            : null,
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      apt.clientName ?? 'Cliente',
                                      style: TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.bold,
                                        decoration: isCancelled
                                            ? TextDecoration.lineThrough
                                            : null,
                                      ),
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 12,
                                      vertical: 6,
                                    ),
                                    decoration: BoxDecoration(
                                      color: AppColors.primaryGold,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      DateFormat('HH:mm').format(apt.dateTime),
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: AppColors.black,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text(
                                apt.serviceName ?? 'Serviço',
                                style: const TextStyle(
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              if (apt.barberName != null)
                                Text(
                                  'Com ${apt.barberName}',
                                  style: const TextStyle(
                                    color: AppColors.textSecondary,
                                    fontSize: 12,
                                  ),
                                ),
                              if (apt.servicePrice != null)
                                Text(
                                  'R\$ ${apt.servicePrice!.toStringAsFixed(2).replaceAll('.', ',')}',
                                  style: const TextStyle(
                                    color: AppColors.primaryGold,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                              if (!isCompleted && !isCancelled) ...[
                                const SizedBox(height: 12),
                                Row(
                                  children: [
                                    Expanded(
                                      child: OutlinedButton.icon(
                                        onPressed: () => _showEditDialog(apt),
                                        icon: const Icon(Icons.edit, size: 18),
                                        label: const Text('Editar'),
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor:
                                              AppColors.textPrimary,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: OutlinedButton.icon(
                                        onPressed: () =>
                                            _cancelAppointment(apt),
                                        icon: const Icon(
                                          Icons.cancel,
                                          size: 18,
                                        ),
                                        label: const Text('Cancelar'),
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: AppColors.error,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: ElevatedButton.icon(
                                        onPressed: () => _markAsDone(apt),
                                        icon: const Icon(Icons.check, size: 18),
                                        label: const Text('Pronto'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: AppColors.success,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                              if (isCompleted)
                                Container(
                                  margin: const EdgeInsets.only(top: 12),
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: AppColors.success.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.check_circle,
                                        color: AppColors.success,
                                        size: 18,
                                      ),
                                      SizedBox(width: 8),
                                      Text(
                                        'CONCLUÍDO',
                                        style: TextStyle(
                                          color: AppColors.success,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              if (isCancelled)
                                Container(
                                  margin: const EdgeInsets.only(top: 12),
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: AppColors.error.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.cancel,
                                        color: AppColors.error,
                                        size: 18,
                                      ),
                                      SizedBox(width: 8),
                                      Text(
                                        'CANCELADO',
                                        style: TextStyle(
                                          color: AppColors.error,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddAppointmentDialog,
        icon: const Icon(Icons.add),
        label: const Text('Novo Agendamento'),
        backgroundColor: AppColors.primaryGold,
        foregroundColor: AppColors.black,
      ),
    );
  }
}
