class Client {
  final String id;
  final String name;
  final String phone;
  final String? email;
  final DateTime? birthDate;
  final String? notes;
  final DateTime createdAt;

  Client({
    required this.id,
    required this.name,
    required this.phone,
    this.email,
    this.birthDate,
    this.notes,
    required this.createdAt,
  });

  factory Client.fromJson(Map<String, dynamic> json) {
    return Client(
      id: json['id'].toString(),
      name: json['name'],
      phone: json['phone'],
      email: json['email'],
      birthDate: json['birth_date'] != null
          ? DateTime.tryParse(json['birth_date'])
          : null,
      notes: json['notes'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'phone': phone,
      'email': email,
      'birthDate': birthDate?.toIso8601String().split('T')[0],
      'notes': notes,
    };
  }

  String get formattedPhone {
    if (phone.length == 11) {
      return '(${phone.substring(0, 2)}) ${phone.substring(2, 7)}-${phone.substring(7)}';
    }
    return phone;
  }

  bool get hasBirthdayToday {
    if (birthDate == null) return false;
    final now = DateTime.now();
    return birthDate!.month == now.month && birthDate!.day == now.day;
  }
}
