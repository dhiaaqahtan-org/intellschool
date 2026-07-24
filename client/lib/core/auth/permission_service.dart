import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../config/config_model.dart';
import '../config/config_provider.dart';

/// Permission service — checks `module:action` permissions against
/// the user's permission set from the bootstrap config.
///
/// Per flutter-implementation-plan.md §4.6: "Permission gate every route and action."
/// Blank permission in the CSV ≠ open — confirm via /config and controller policies.
class PermissionService {
  final UserConfig? _user;

  PermissionService(this._user);

  /// Returns true if the user has the given permission (e.g. 'student:mark-attendance').
  bool can(String permission) {
    if (permission.isEmpty) return false;
    final perms = _user?.permissions ?? [];
    // Admin role typically has '*' wildcard
    if (perms.contains('*')) return true;
    return perms.contains(permission);
  }

  /// Returns true if the user has ANY of the given permissions.
  bool canAny(List<String> permissions) {
    return permissions.any(can);
  }

  /// Returns true if the user has ALL of the given permissions.
  bool canAll(List<String> permissions) {
    return permissions.every(can);
  }

  /// Returns true if the user's role matches the given role name.
  bool hasRole(String roleName) {
    return _user?.roleName?.toLowerCase() == roleName.toLowerCase();
  }

  /// Returns true if the user has access to a menu item (checks permission field).
  bool canAccessMenu(MenuItemConfig item) {
    // Items without a permission requirement are accessible to all authenticated users
    final perm = item.permission;
    if (perm == null || perm.isEmpty) return true;
    return can(perm);
  }

  /// Filters a list of menu items to only those the user can access.
  List<MenuItemConfig> filterMenu(List<MenuItemConfig> items) {
    return items.where(canAccessMenu).toList();
  }
}

final permissionServiceProvider = Provider<PermissionService>((ref) {
  final config = ref.watch(configProvider);
  return PermissionService(config?.user);
});
