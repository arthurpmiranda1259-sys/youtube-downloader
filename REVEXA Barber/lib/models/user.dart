class User {
  final String id;
  final String username;
  final String role;
  final String fullName;
  final String? barbershopId;

  User({
    required this.id,
    required this.username,
    required this.role,
    required this.fullName,
    this.barbershopId,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'].toString(),
      username: json['username'],
      role: json['role'],
      fullName: json['fullName'] ?? json['full_name'] ?? '',
      barbershopId: json['barbershopId']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'role': role,
      'fullName': fullName,
      'barbershopId': barbershopId,
    };
  }

  bool get isAdmin => role == 'admin';
  bool get isOwner => role == 'owner';
}
