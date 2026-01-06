import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl =
      'https://revexa.com.br/revexa_sistemas/Sistemas/Revexa_Barber/api.php';

  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Future<Map<String, String>> _getHeaders() async {
    final token = await _getToken();
    return {
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // Auth
  Future<Map<String, dynamic>> login(String username, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({'username': username, 'password': password}),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      final body = json.decode(response.body);
      throw Exception(body['error'] ?? 'Falha no login');
    }
  }

  // Dashboard
  Future<Map<String, dynamic>> getDashboard() async {
    final response = await http.get(
      Uri.parse('$baseUrl/dashboard'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return {};
  }

  // Clients
  Future<List<dynamic>> getClients() async {
    final response = await http.get(
      Uri.parse('$baseUrl/clients'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return [];
  }

  Future<Map<String, dynamic>> createClient(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/clients'),
      headers: await _getHeaders(),
      body: json.encode(data),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao criar cliente');
    }
  }

  Future<void> updateClient(String id, Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/clients'),
      headers: await _getHeaders(),
      body: json.encode({...data, 'id': id}),
    );

    if (response.statusCode != 200) {
      throw Exception('Erro ao atualizar cliente');
    }
  }

  Future<void> deleteClient(String id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/clients?id=$id'),
      headers: await _getHeaders(),
    );

    if (response.statusCode != 200) {
      throw Exception('Erro ao excluir cliente');
    }
  }

  // Services
  Future<List<dynamic>> getServices() async {
    final response = await http.get(
      Uri.parse('$baseUrl/services'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return [];
  }

  Future<Map<String, dynamic>> createService(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/services'),
      headers: await _getHeaders(),
      body: json.encode(data),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao criar serviço');
    }
  }

  Future<Map<String, dynamic>> createUser(Map<String, dynamic> userData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/users'),
      headers: await _getHeaders(),
      body: json.encode(userData),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao criar usuário');
    }
  }

  Future<void> updateBarber(String id, Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/barbers/$id'), // Corrected URL
      headers: await _getHeaders(),
      body: json.encode(data),
    );

    if (response.statusCode != 200) {
      throw Exception('Erro ao atualizar barbeiro');
    }
  }

  Future<void> deleteBarber(String id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/barbers/$id'), // Corrected URL
      headers: await _getHeaders(),
    );

    if (response.statusCode != 200) {
      throw Exception('Erro ao excluir barbeiro');
    }
  }

  // Barber Availability
  Future<List<dynamic>> getBarberAvailability(String barberId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/barbers/$barberId/availability'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Falha ao carregar disponibilidade.');
    }
  }

  Future<void> updateBarberAvailability(String barberId, List<Map<String, dynamic>> availability) async {
    final response = await http.put(
      Uri.parse('$baseUrl/barbers/$barberId/availability'),
      headers: await _getHeaders(),
      body: json.encode(availability),
    );

    if (response.statusCode != 200) {
      throw Exception('Falha ao atualizar disponibilidade.');
    }
  }

  // Appointments
  Future<List<dynamic>> getAppointments(String date) async {
    final response = await http.get(
      Uri.parse('$baseUrl/appointments?date=$date'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return [];
  }

  Future<Map<String, dynamic>> createAppointment(
    Map<String, dynamic> data,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/appointments'),
      headers: await _getHeaders(),
      body: json.encode(data),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao criar agendamento');
    }
  }

  Future<Map<String, dynamic>> updateAppointmentStatus(
    String id,
    String status,
  ) async {
    final response = await http.put(
      Uri.parse('$baseUrl/appointments'),
      headers: await _getHeaders(),
      body: json.encode({'id': id, 'status': status}),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao atualizar status');
    }
  }

  Future<void> updateAppointment(String id, Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/appointments'),
      headers: await _getHeaders(),
      body: json.encode({...data, 'id': id}),
    );

    if (response.statusCode != 200) {
      throw Exception('Erro ao atualizar agendamento');
    }
  }

  Future<void> updateService(String id, Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/services'),
      headers: await _getHeaders(),
      body: json.encode({...data, 'id': id}),
    );

    if (response.statusCode != 200) {
      throw Exception('Erro ao atualizar serviço');
    }
  }

  Future<void> deleteService(String id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/services?id=$id'),
      headers: await _getHeaders(),
    );

    if (response.statusCode != 200) {
      throw Exception('Erro ao excluir serviço');
    }
  }

  // Barbers
  Future<List<dynamic>> getBarbers() async {
    final response = await http.get(
      Uri.parse('$baseUrl/barbers'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return [];
  }

  Future<Map<String, dynamic>> createBarber(Map<String, dynamic> data) async {
    print('DEBUG: Sending barber data: $data');
    final headers = await _getHeaders();
    print('DEBUG: Headers: $headers');

    final response = await http.post(
      Uri.parse('$baseUrl/barbers'),
      headers: headers,
      body: json.encode(data),
    );

    print('DEBUG: Response status: ${response.statusCode}');
    print('DEBUG: Response body: ${response.body}');

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(
        'Erro ao criar barbeiro (${response.statusCode}): ${response.body}',
      );
    }
  }

  // Payments
  Future<List<dynamic>> getPayments(String startDate, String endDate) async {
    final response = await http.get(
      Uri.parse('$baseUrl/payments?start_date=$startDate&end_date=$endDate'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return [];
  }

  Future<Map<String, dynamic>> createPayment(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/payments'),
      headers: await _getHeaders(),
      body: json.encode(data),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao registrar pagamento');
    }
  }

  // Reports
  Future<Map<String, dynamic>> getReports(
    String startDate,
    String endDate,
  ) async {
    final response = await http.get(
      Uri.parse('$baseUrl/reports?start_date=$startDate&end_date=$endDate'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return {};
  }

  // Settings
  Future<Map<String, dynamic>> getSettings() async {
    final response = await http.get(
      Uri.parse('$baseUrl/settings'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return {};
  }

  Future<Map<String, dynamic>> updateSettings(Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/settings'),
      headers: await _getHeaders(),
      body: json.encode(data),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao atualizar configurações');
    }
  }

  // Version Check
  Future<Map<String, dynamic>> checkVersion() async {
    final response = await http.get(Uri.parse('$baseUrl/version'));
    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    return {};
  }

  // Admin
  Future<Map<String, dynamic>> createBarbershop(
    Map<String, dynamic> data,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/users'),
      headers: await _getHeaders(),
      body: json.encode(data),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao criar barbearia');
    }
  }
}
