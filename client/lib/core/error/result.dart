import 'package:fpdart/fpdart.dart';

import 'failures.dart';

/// Result type used by all repository methods.
/// `Result<T> = Either<Failure, T>`
typedef Result<T> = Either<Failure, T>;

/// Convenience constructors.
abstract final class Results {
  /// Wrap a successful value.
  static Result<T> ok<T>(T value) => Right(value);

  /// Wrap a failure.
  static Result<T> err<T>(Failure failure) => Left(failure);

  /// Wrap a try-catch block into a Result.
  static Future<Result<T>> guard<T>(Future<T> Function() fn) async {
    try {
      return Right(await fn());
    } on Exception catch (e) {
      return Left(UnknownFailure(message: e.toString()));
    }
  }
}
