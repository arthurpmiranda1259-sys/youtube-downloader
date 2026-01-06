import 'package:mysql1/mysql1.dart';

class Database {
  static final Database _instance = Database._internal();
  MySqlConnection? _conn;

  factory Database() {
    return _instance;
  }

  Database._internal();

  Future<void> connect() async {
    final settings = ConnectionSettings(
      host: '127.0.0.1', // Change if DB is on another host
      port: 3306,
      user: 'revexa01',
      password: 'mamaco12',
      db: 'revexa01',
    );

    try {
      _conn = await MySqlConnection.connect(settings);
      print('Connected to database');
    } catch (e) {
      print('Error connecting to database: $e');
      rethrow;
    }
  }

  MySqlConnection get connection {
    if (_conn == null) {
      throw Exception('Database not connected');
    }
    return _conn!;
  }
}
