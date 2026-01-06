import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../theme/app_colors.dart';
import '../models/client.dart';
import '../models/appointment.dart';
import '../services/api_service.dart';

class ClientHistoryScreen extends StatefulWidget {
  final Client client;

  const ClientHistoryScreen({super.key, required this.client});

  @override
  State<ClientHistoryScreen> createState() => _ClientHistoryScreenState();
}

class _ClientHistoryScreenState extends State<ClientHistoryScreen> {
  final ApiService _api = ApiService();
  List<Appointment> _appointments = [];
  bool _isLoading = true;
  double _totalSpent = 0;
  int _totalVisits = 0;
  DateTime? _lastVisit;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    setState(() => _isLoading = true);
    try {
      // Get appointments for the last 365 days
      final dates = <String>[];
      for (int i = 0; i < 365; i++) {
        final date = DateTime.now().subtract(Duration(days: i));
        dates.add(
          '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}',
        );
      }

      List<Appointment> allAppointments = [];
      for (final date in dates) {
        try {
          final data = await _api.getAppointments(date);
          final dayAppointments = (data)
              .map((json) => Appointment.fromJson(json))
              .where((apt) => apt.clientId == widget.client.id)
              .toList();
          allAppointments.addAll(dayAppointments);
        } catch (e) {
          // Continue to next date if error
        }
      }

      // Sort by date descending
      allAppointments.sort((a, b) => b.dateTime.compareTo(a.dateTime));

      // Calculate stats
      final completed = allAppointments
          .where((apt) => apt.status == 'completed')
          .toList();

      double total = 0;
      for (var apt in completed) {
        if (apt.servicePrice != null) {
          total += apt.servicePrice!;
        }
      }

      setState(() {
        _appointments = allAppointments;
        _totalVisits = completed.length;
        _totalSpent = total;
        _lastVisit = completed.isNotEmpty ? completed.first.dateTime : null;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.client.name),
        backgroundColor: AppColors.black,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadHistory,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Client info card
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          children: [
                            CircleAvatar(
                              radius: 40,
                              backgroundColor: AppColors.primaryGold,
                              child: Text(
                                widget.client.name[0].toUpperCase(),
                                style: const TextStyle(
                                  color: AppColors.black,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 32,
                                ),
                              ),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              widget.client.name,
                              style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              widget.client.formattedPhone,
                              style: const TextStyle(
                                color: AppColors.textSecondary,
                              ),
                            ),
                            if (widget.client.birthDate != null) ...[
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(
                                    Icons.cake,
                                    size: 16,
                                    color: AppColors.textSecondary,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    DateFormat(
                                      'dd/MM/yyyy',
                                    ).format(widget.client.birthDate!),
                                    style: const TextStyle(
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Stats cards
                    Row(
                      children: [
                        Expanded(
                          child: Card(
                            color: AppColors.success.withOpacity(0.1),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                children: [
                                  const Icon(
                                    Icons.attach_money,
                                    color: AppColors.success,
                                    size: 32,
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    'R\$ ${_totalSpent.toStringAsFixed(2).replaceAll('.', ',')}',
                                    style: const TextStyle(
                                      fontSize: 20,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.success,
                                    ),
                                  ),
                                  const Text(
                                    'Total Gasto',
                                    style: TextStyle(
                                      color: AppColors.textSecondary,
                                      fontSize: 12,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Card(
                            color: AppColors.info.withOpacity(0.1),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                children: [
                                  const Icon(
                                    Icons.event_available,
                                    color: AppColors.info,
                                    size: 32,
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    '$_totalVisits',
                                    style: const TextStyle(
                                      fontSize: 20,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.info,
                                    ),
                                  ),
                                  const Text(
                                    'Visitas',
                                    style: TextStyle(
                                      color: AppColors.textSecondary,
                                      fontSize: 12,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),

                    // Last visit card
                    if (_lastVisit != null)
                      Card(
                        color: AppColors.warning.withOpacity(0.1),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.schedule,
                                color: AppColors.warning,
                              ),
                              const SizedBox(width: 12),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Última Visita',
                                    style: TextStyle(
                                      color: AppColors.textSecondary,
                                      fontSize: 12,
                                    ),
                                  ),
                                  Text(
                                    DateFormat(
                                      'dd/MM/yyyy',
                                    ).format(_lastVisit!),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    const SizedBox(height: 24),

                    // History title
                    const Text(
                      'Histórico de Agendamentos',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Appointments list
                    if (_appointments.isEmpty)
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.all(32),
                          child: Text(
                            'Nenhum agendamento encontrado',
                            style: TextStyle(color: AppColors.textSecondary),
                          ),
                        ),
                      )
                    else
                      ..._appointments.map((apt) {
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          child: ListTile(
                            leading: Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: _getStatusColor(
                                  apt.status,
                                ).withOpacity(0.2),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Icon(
                                _getStatusIcon(apt.status),
                                color: _getStatusColor(apt.status),
                              ),
                            ),
                            title: Text(apt.serviceName ?? 'Serviço'),
                            subtitle: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  DateFormat(
                                    'dd/MM/yyyy • HH:mm',
                                  ).format(apt.dateTime),
                                ),
                                if (apt.barberName != null)
                                  Text(
                                    'Com ${apt.barberName}',
                                    style: const TextStyle(fontSize: 12),
                                  ),
                              ],
                            ),
                            trailing: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                if (apt.servicePrice != null)
                                  Text(
                                    'R\$ ${apt.servicePrice!.toStringAsFixed(2).replaceAll('.', ',')}',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16,
                                    ),
                                  ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 2,
                                  ),
                                  decoration: BoxDecoration(
                                    color: _getStatusColor(
                                      apt.status,
                                    ).withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    _getStatusLabel(apt.status),
                                    style: TextStyle(
                                      color: _getStatusColor(apt.status),
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }),
                  ],
                ),
              ),
            ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'completed':
        return AppColors.success;
      case 'cancelled':
        return AppColors.error;
      case 'in_progress':
        return AppColors.warning;
      default:
        return AppColors.info;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'completed':
        return Icons.check_circle;
      case 'cancelled':
        return Icons.cancel;
      case 'in_progress':
        return Icons.timer;
      default:
        return Icons.schedule;
    }
  }

  String _getStatusLabel(String status) {
    switch (status) {
      case 'completed':
        return 'CONCLUÍDO';
      case 'cancelled':
        return 'CANCELADO';
      case 'in_progress':
        return 'EM ANDAMENTO';
      case 'scheduled':
        return 'AGENDADO';
      default:
        return status.toUpperCase();
    }
  }
}
