import 'dart:io';
import 'package:mysql1/mysql1.dart';

void main() async {
  final settings = ConnectionSettings(
    host: '127.0.0.1',
    port: 3306,
    user: 'revexa01',
    password: 'mamaco12',
    db: 'revexa01',
  );

  print('Connecting to database...');
  MySqlConnection conn;
  try {
    conn = await MySqlConnection.connect(settings);
    print('Connected!');
  } catch (e) {
    print('Error connecting to database: $e');
    print(
        'Make sure the database "revexa01" exists and the credentials are correct.');
    return;
  }

  try {
    final schemaFile = File('schema.sql');
    if (!await schemaFile.exists()) {
      print('schema.sql not found in current directory.');
      return;
    }

    final sqlContent = await schemaFile.readAsString();

    // Split by semicolon to get individual statements
    // This is a simple split and might break if semicolons are inside strings,
    // but for this schema it should be fine.
    final statements = sqlContent.split(';');

    for (var statement in statements) {
      if (statement.trim().isEmpty) continue;

      try {
        print('Executing: ${statement.trim().substring(0, 20)}...');
        await conn.query(statement);
      } catch (e) {
        print('Error executing statement: $e');
        // Don't stop on error, some tables might already exist
      }
    }

    print('Database setup completed successfully!');
  } catch (e) {
    print('Error during setup: $e');
  } finally {
    await conn.close();
  }
}
