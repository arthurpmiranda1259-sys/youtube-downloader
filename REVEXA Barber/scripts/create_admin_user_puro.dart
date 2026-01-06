import 'api_service_puro.dart';

void main() async {
  final api = ApiServicePuro();
  final userData = {
    'name': 'Novo Admin',
    'password': 'admin1234',
    'barbershopName': 'Barbearia Nova',
    'barbershopPhone': '(99) 99999-9999',
  };
  try {
    final result = await api.createUser(userData);
    print('Usuário admin criado:');
    print(result);
  } catch (e) {
    print('Erro ao criar admin:');
    print(e);
  }
}
