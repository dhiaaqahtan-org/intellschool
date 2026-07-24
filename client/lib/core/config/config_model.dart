import 'package:freezed_annotation/freezed_annotation.dart';

part 'config_model.freezed.dart';
part 'config_model.g.dart';

/// Bootstrap configuration fetched from `GET /api/v1/config`.
///
/// This is the single source of truth for:
/// - Current user info + permissions
/// - Enabled modules
/// - Menu structure
/// - Enums (attendance types, statuses, etc.)
/// - Currency, locale, organization info
///
/// WARNING: The exact JSON shape must be captured from a real InstiKit
/// response (per flutter-implementation-plan.md §6.4) before generating
/// the final model. The fields below are based on known InstiKit patterns.
@freezed
class AppConfig with _$AppConfig {
  const factory AppConfig({
    /// Current authenticated user.
    @JsonKey(name: 'user') UserConfig? user,

    /// Organization / institute info.
    @JsonKey(name: 'organization') OrganizationConfig? organization,

    /// Enabled module slugs.
    @JsonKey(name: 'modules') @Default([]) List<String> modules,

    /// Navigation menu items, already permission-filtered by the server.
    @JsonKey(name: 'menu') @Default([]) List<MenuItemConfig> menu,

    /// Currency symbol/code.
    @JsonKey(name: 'currency') String? currency,

    /// Default locale.
    @JsonKey(name: 'locale') @Default('ar') String locale,

    /// Date format pattern.
    @JsonKey(name: 'date_format') @Default('Y-m-d') String dateFormat,

    /// Time format pattern.
    @JsonKey(name: 'time_format') @Default('H:i') String timeFormat,

    /// Academic period context.
    @JsonKey(name: 'period') PeriodConfig? period,

    /// Dynamic enums/lookups (attendance types, statuses, etc.).
    @JsonKey(name: 'enums') @Default({}) Map<String, dynamic> enums,

    /// Whether the app is in maintenance mode.
    @JsonKey(name: 'maintenance') @Default(false) bool maintenance,

    /// FCM / Push notification config.
    @JsonKey(name: 'push_config') PushConfig? pushConfig,

    /// Custom field definitions per module.
    @JsonKey(name: 'custom_fields') @Default({}) Map<String, dynamic> customFields,
  }) = _AppConfig;

  factory AppConfig.fromJson(Map<String, dynamic> json) => _$AppConfigFromJson(json);
}

@freezed
class UserConfig with _$UserConfig {
  const factory UserConfig({
    @JsonKey(name: 'id') required String id,
    @JsonKey(name: 'uuid') String? uuid,
    @JsonKey(name: 'name') required String name,
    @JsonKey(name: 'email') String? email,
    @JsonKey(name: 'phone') String? phone,
    @JsonKey(name: 'avatar') String? avatar,
    @JsonKey(name: 'role_id') String? roleId,
    @JsonKey(name: 'role_name') String? roleName,
    @JsonKey(name: 'team_id') String? teamId,
    @JsonKey(name: 'permissions') @Default([]) List<String> permissions,
    @JsonKey(name: 'preferences') @Default({}) Map<String, dynamic> preferences,
  }) = _UserConfig;

  factory UserConfig.fromJson(Map<String, dynamic> json) => _$UserConfigFromJson(json);
}

@freezed
class OrganizationConfig with _$OrganizationConfig {
  const factory OrganizationConfig({
    @JsonKey(name: 'id') String? id,
    @JsonKey(name: 'name') String? name,
    @JsonKey(name: 'logo') String? logo,
    @JsonKey(name: 'address') String? address,
    @JsonKey(name: 'phone') String? phone,
    @JsonKey(name: 'email') String? email,
    @JsonKey(name: 'website') String? website,
  }) = _OrganizationConfig;

  factory OrganizationConfig.fromJson(Map<String, dynamic> json) => _$OrganizationConfigFromJson(json);
}

@freezed
class MenuItemConfig with _$MenuItemConfig {
  const factory MenuItemConfig({
    @JsonKey(name: 'label') required String label,
    @JsonKey(name: 'route') required String route,
    @JsonKey(name: 'icon') String? icon,
    @JsonKey(name: 'permission') String? permission,
    @JsonKey(name: 'sort') @Default(0) int sort,
    @JsonKey(name: 'children') @Default([]) List<MenuItemConfig> children,
  }) = _MenuItemConfig;

  factory MenuItemConfig.fromJson(Map<String, dynamic> json) => _$MenuItemConfigFromJson(json);
}

@freezed
class PeriodConfig with _$PeriodConfig {
  const factory PeriodConfig({
    @JsonKey(name: 'id') String? id,
    @JsonKey(name: 'name') String? name,
    @JsonKey(name: 'start_date') String? startDate,
    @JsonKey(name: 'end_date') String? endDate,
  }) = _PeriodConfig;

  factory PeriodConfig.fromJson(Map<String, dynamic> json) => _$PeriodConfigFromJson(json);
}

@freezed
class PushConfig with _$PushConfig {
  const factory PushConfig({
    @JsonKey(name: 'pusher_key') String? pusherKey,
    @JsonKey(name: 'pusher_cluster') String? pusherCluster,
    @JsonKey(name: 'fcm_enabled') @Default(false) bool fcmEnabled,
  }) = _PushConfig;

  factory PushConfig.fromJson(Map<String, dynamic> json) => _$PushConfigFromJson(json);
}
