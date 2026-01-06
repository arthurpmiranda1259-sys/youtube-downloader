import 'dart:convert';
import 'dart:io';
import 'package:crypto/crypto.dart';
import 'package:shelf/shelf.dart';
import 'package:shelf/shelf_io.dart' as shelf_io;
import 'package:shelf_router/shelf_router.dart';
import 'package:revexa_backend/database.dart';
import 'package:jaguar_jwt/jaguar_jwt.dart';
import 'package:shelf_hotreload/shelf_hotreload.dart';

// Secret key for JWT (Change this in production)
const String kJwtSecret = 'revexa_secret_key_12345';

void main() {
  withHotreload(() => createServer());
}

Future<HttpServer> createServer() async {
  // Connect to database
  final db = Database();
  await db.connect();

  final app = Router();

  // Login Endpoint
  app.post('/login', (Request request) async {
    final payload = await request.readAsString();
    print('Login attempt payload: $payload'); // Debug print

    final data = json.decode(payload);
    final username = data['username'];
    final password = data['password'];

    // Hash password
    final bytes = utf8.encode(password);
    final digest = sha256.convert(bytes);
    final passwordHash = digest.toString();

    print('Checking user: $username with hash: $passwordHash'); // Debug print

    final results = await db.connection.query(
      'SELECT id, role, full_name FROM users WHERE username = ? AND password_hash = ?',
      [username, passwordHash],
    );

    if (results.isNotEmpty) {
      print('User found: ${results.first['username']}'); // Debug print
      final user = results.first;
      final claimSet = JwtClaim(
        subject: user['id'].toString(),
        issuer: 'revexa_backend',
        otherClaims: <String, dynamic>{
          'role': user['role'],
          'username': username,
        },
        maxAge: const Duration(days: 1),
      );
      final token = issueJwtHS256(claimSet, kJwtSecret);

      // Get barbershop ID if owner
      int? barbershopId;
      if (user['role'] == 'owner') {
        final shopResults = await db.connection.query(
          'SELECT id FROM barbershops WHERE owner_id = ?',
          [user['id']],
        );
        if (shopResults.isNotEmpty) {
          barbershopId = shopResults.first['id'];
        }
      }

      return Response.ok(json.encode({
        'token': token,
        'user': {
          'id': user['id'],
          'username': username,
          'role': user['role'],
          'fullName': user['full_name'],
          'barbershopId': barbershopId,
        }
      }));
    } else {
      return Response.forbidden(json.encode({'error': 'Invalid credentials'}));
    }
  });

  // Admin: Create User (Barbershop Owner)
  app.post('/admin/users', (Request request) async {
    // Verify Admin Token
    final authHeader = request.headers['Authorization'];
    if (authHeader == null) return Response.forbidden('Missing token');

    try {
      final token = authHeader.replaceFirst('Bearer ', '');
      final claimSet = verifyJwtHS256Signature(token, kJwtSecret);
      if (claimSet['role'] != 'admin') {
        return Response.forbidden('Not authorized');
      }
    } catch (e) {
      return Response.forbidden('Invalid token');
    }

    final payload = await request.readAsString();
    final data = json.decode(payload);

    final username = data['username'];
    final password = data['password'];
    final fullName = data['fullName'];
    final barbershopName = data['barbershopName'];

    // Hash password
    final bytes = utf8.encode(password);
    final digest = sha256.convert(bytes);
    final passwordHash = digest.toString();

    try {
      await db.connection.transaction((ctx) async {
        final result = await ctx.query(
          'INSERT INTO users (username, password_hash, role, full_name) VALUES (?, ?, ?, ?)',
          [username, passwordHash, 'owner', fullName],
        );
        final userId = result.insertId;

        await ctx.query(
          'INSERT INTO barbershops (name, owner_id) VALUES (?, ?)',
          [barbershopName, userId],
        );
      });

      return Response.ok(json.encode({'message': 'User created successfully'}));
    } catch (e) {
      return Response.internalServerError(
          body: json.encode({'error': e.toString()}));
    }
  });

  // Get Clients (Protected)
  app.get('/clients', (Request request) async {
    final authHeader = request.headers['Authorization'];
    if (authHeader == null) return Response.forbidden('Missing token');

    try {
      final token = authHeader.replaceFirst('Bearer ', '');
      final claimSet = verifyJwtHS256Signature(token, kJwtSecret);
      final userId = int.parse(claimSet.subject!);
      final role = claimSet['role'];

      if (role == 'owner') {
        // Get owner's barbershop
        final shopResults = await db.connection.query(
          'SELECT id FROM barbershops WHERE owner_id = ?',
          [userId],
        );

        if (shopResults.isEmpty) return Response.ok(json.encode([]));
        final shopId = shopResults.first['id'];

        final results = await db.connection.query(
          'SELECT * FROM clients WHERE barbershop_id = ?',
          [shopId],
        );

        final clients = results
            .map((row) => {
                  'id': row['id'],
                  'name': row['name'],
                  'phone': row['phone'],
                  'email': row['email'],
                  // Add other fields
                })
            .toList();

        return Response.ok(json.encode(clients));
      } else {
        // Admin sees all? Or nothing? Let's say admin manages users, not clients for now.
        return Response.ok(json.encode([]));
      }
    } catch (e) {
      return Response.forbidden('Invalid token');
    }
  });

  // Create Review
  app.post('/reviews', (Request request) async {
    // Optional: Verify token if you want only auth users to post
    // final authHeader = request.headers['Authorization'];
    // if (authHeader == null) return Response.forbidden('Missing token');

    try {
      final payload = await request.readAsString();
      final data = json.decode(payload);

      final appointmentId = int.parse(data['appointmentId'].toString());
      final rating = data['rating'];
      final comment = data['comment'];
      final tags = json.encode(data['tags']);

      // Get client_id from appointment
      final apptResults = await db.connection.query(
        'SELECT client_id FROM appointments WHERE id = ?',
        [appointmentId],
      );

      if (apptResults.isEmpty) {
        return Response.notFound(
            json.encode({'error': 'Appointment not found'}));
      }

      final clientId = apptResults.first['client_id'];

      await db.connection.query(
        'INSERT INTO reviews (appointment_id, client_id, rating, comment, tags) VALUES (?, ?, ?, ?, ?)',
        [appointmentId, clientId, rating, comment, tags],
      );

      return Response.ok(
          json.encode({'message': 'Review created successfully'}));
    } catch (e) {
      print(e);
      return Response.internalServerError(
          body: json.encode({'error': e.toString()}));
    }
  });

  // Get Reviews (Protected)
  app.get('/reviews', (Request request) async {
    final authHeader = request.headers['Authorization'];
    if (authHeader == null) return Response.forbidden('Missing token');

    try {
      final token = authHeader.replaceFirst('Bearer ', '');
      final claimSet = verifyJwtHS256Signature(token, kJwtSecret);
      final userId = int.parse(claimSet.subject!);
      final role = claimSet['role'];

      if (role == 'owner') {
        // Get owner's barbershop
        final shopResults = await db.connection.query(
          'SELECT id FROM barbershops WHERE owner_id = ?',
          [userId],
        );

        if (shopResults.isEmpty) return Response.ok(json.encode([]));
        final shopId = shopResults.first['id'];

        // Get reviews for appointments in this barbershop
        final results = await db.connection.query(
          '''
          SELECT r.*, c.name as client_name 
          FROM reviews r
          JOIN appointments a ON r.appointment_id = a.id
          JOIN clients c ON r.client_id = c.id
          WHERE a.barbershop_id = ?
          ORDER BY r.created_at DESC
          ''',
          [shopId],
        );

        final reviews = results.map((row) {
          return {
            'id': row['id'].toString(),
            'appointmentId': row['appointment_id'].toString(),
            'client': {
              'id': row['client_id'].toString(),
              'name': row['client_name'],
            },
            'rating': row['rating'],
            'comment': row['comment'],
            'tags': json.decode(row['tags'].toString()),
            'createdAt': row['created_at'].toString(),
          };
        }).toList();

        return Response.ok(json.encode(reviews));
      }
      return Response.ok(json.encode([]));
    } catch (e) {
      print(e);
      return Response.forbidden('Invalid token');
    }
  });

  // Add more endpoints as needed...

  final handler = Pipeline().addMiddleware((innerHandler) {
    return (request) async {
      if (request.method == 'OPTIONS') {
        return Response.ok('', headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
          'Access-Control-Allow-Headers': 'Origin, Content-Type, Authorization',
        });
      }
      final response = await innerHandler(request);
      return response.change(headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers': 'Origin, Content-Type, Authorization',
      });
    };
  }).addHandler(app.call);

  final server = await shelf_io.serve(handler, InternetAddress.anyIPv4, 8080);
  print('Server running on localhost:${server.port}');
  return server;
}
