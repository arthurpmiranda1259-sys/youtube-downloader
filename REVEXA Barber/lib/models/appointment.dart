class Appointment {
  final String id;
  final String clientId;
  final String clientName;
  final String serviceId;
  final String serviceName;
  final double? servicePrice;
  final int serviceDuration;
  final String barberId;
  final String? barberName;
  final DateTime dateTime;
  final String status;
  final String? notes;

  Appointment({
    required this.id,
    required this.clientId,
    required this.clientName,
    required this.serviceId,
    required this.serviceName,
    this.servicePrice,
    required this.serviceDuration,
    required this.barberId,
    this.barberName,
    required this.dateTime,
    required this.status,
    this.notes,
  });

  factory Appointment.fromJson(Map<String, dynamic> json) {
    // Parse price defensively (handle string, number, or null)
    double? parsedPrice;
    try {
      if (json['price'] != null) {
        final priceStr = json['price'].toString().replaceAll(',', '.');
        parsedPrice = double.tryParse(priceStr) ?? 0.0;
      }
    } catch (e) {
      parsedPrice = 0.0;
    }

    return Appointment(
      id: json['id'].toString(),
      clientId: json['client_id'].toString(),
      clientName: json['client_name'] ?? '',
      serviceId: json['service_id'].toString(),
      serviceName: json['service_name'] ?? '',
      servicePrice: parsedPrice,
      serviceDuration: int.parse(json['duration_minutes']?.toString() ?? '0'),
      barberId: json['barber_id']?.toString() ?? '1',
      barberName: json['barber_name'],
      dateTime: DateTime.parse(json['appointment_date']),
      status: json['status'] ?? 'pending',
      notes: json['notes'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'clientId': clientId,
      'serviceId': serviceId,
      'date': dateTime.toIso8601String(),
      'notes': notes,
    };
  }

  String get formattedTime {
    final hour = dateTime.hour.toString().padLeft(2, '0');
    final minute = dateTime.minute.toString().padLeft(2, '0');
    return '$hour:$minute';
  }

  String get formattedPrice => servicePrice != null
      ? 'R\$ ${servicePrice!.toStringAsFixed(2).replaceAll('.', ',')}'
      : 'R\$ 0,00';

  bool get isPending => status == 'scheduled';
  bool get isCompleted => status == 'completed';
  bool get isCancelled => status == 'cancelled';

  String get formattedDateTime {
    final date = dateTime;
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year} às ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
  }

  String get formattedStatus {
    switch (status) {
      case 'scheduled':
        return 'Agendado';
      case 'completed':
        return 'Concluído';
      case 'cancelled':
        return 'Cancelado';
      default:
        return status;
    }
  }
}
