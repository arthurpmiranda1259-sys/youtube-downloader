class Service {
  final String id;
  final String name;
  final String description;
  final double price;
  final int durationMinutes;
  final String? category;
  final bool isActive;

  Service({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.durationMinutes,
    this.category,
    this.isActive = true,
  });

  factory Service.fromJson(Map<String, dynamic> json) {
    return Service(
      id: json['id'].toString(),
      name: json['name'],
      description: json['description'] ?? '',
      price: double.parse(json['price'].toString()),
      durationMinutes: int.parse(json['duration_minutes'].toString()),
      category: json['category'],
      isActive: json['is_active'] == 1 || json['is_active'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'description': description,
      'price': price,
      'durationMinutes': durationMinutes,
      'category': category,
    };
  }

  String get formattedPrice =>
      'R\$ ${price.toStringAsFixed(2).replaceAll('.', ',')}';
  String get formattedDuration => '$durationMinutes min';
}
