class Barber {
  final String id;
  final String name;
  final String phone;
  final double commissionPercentage;
  final bool isActive;
  final String? pixKey;

  Barber({
    required this.id,
    required this.name,
    required this.phone,
    required this.commissionPercentage,
    this.isActive = true,
    this.pixKey,
  });

  factory Barber.fromJson(Map<String, dynamic> json) {
    return Barber(
      id: json['id'].toString(),
      name: json['name'],
      phone: json['phone'] ?? '',
      commissionPercentage: double.parse(
        json['commission_percentage']?.toString() ?? '50',
      ),
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      pixKey: json['pix_key'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'phone': phone,
      'commission_percentage': commissionPercentage,
      'pix_key': pixKey,
    };
  }

  String get formattedPhone {
    if (phone.length == 11) {
      return '(${phone.substring(0, 2)}) ${phone.substring(2, 7)}-${phone.substring(7)}';
    } else if (phone.length == 10) {
      return '(${phone.substring(0, 2)}) ${phone.substring(2, 6)}-${phone.substring(6)}';
    }
    return phone;
  }

  String get formattedCommission =>
      '${commissionPercentage.toStringAsFixed(0)}%';
}
