import 'package:sqflite/sqflite.dart';
import 'local_database_service.dart';

/// Legacy alias for LocalDatabaseService.
class DbService {
  DbService._();

  static Future<Database> get database => LocalDatabaseService.instance.database;
}
