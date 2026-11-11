import 'package:flutter/material.dart';
import '../../utils/app_constants.dart';

class LoginInputField extends StatelessWidget {
  final TextEditingController controller;
  final FocusNode focusNode;
  final FocusNode? nextFocus;
  final IconData icon;
  final String placeholder;
  final bool isPassword;
  final bool isPasswordVisible;
  final VoidCallback onTogglePassword;
  final VoidCallback? onSubmit;

  const LoginInputField({
    super.key,
    required this.controller,
    required this.focusNode,
    this.nextFocus,
    required this.icon,
    required this.placeholder,
    required this.isPassword,
    required this.isPasswordVisible,
    required this.onTogglePassword,
    this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;
    final baseRadius = BorderRadius.circular(28);

    return TextFormField(
      controller: controller,
      focusNode: focusNode,
      obscureText: isPassword && !isPasswordVisible,
      textInputAction:
          nextFocus != null ? TextInputAction.next : TextInputAction.done,
      autocorrect: false,
      enableSuggestions: !isPassword,
      style: tt.bodyMedium?.copyWith(color: const Color(0xFF1E293B), fontSize: 15),
      onFieldSubmitted: (_) {
        if (nextFocus != null) {
          FocusScope.of(context).requestFocus(nextFocus);
        } else {
          onSubmit?.call();
        }
      },
      validator: (v) {
        if (v == null || v.trim().isEmpty) {
          return isPassword ? 'Password wajib diisi' : 'Username/NIS wajib diisi';
        }
        return null;
      },
      decoration: InputDecoration(
        filled: true,
        fillColor: Colors.white,
        hintText: placeholder,
        hintStyle: tt.bodyMedium?.copyWith(
            color: GaraColors.studentTextMuted, fontSize: 14),
        prefixIcon: Icon(icon, color: GaraColors.studentTextMuted, size: 18),
        suffixIcon: isPassword
            ? IconButton(
                icon: Icon(
                  isPasswordVisible
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: GaraColors.studentTextMuted,
                  size: 18,
                ),
                onPressed: onTogglePassword,
              )
            : null,
        contentPadding: const EdgeInsets.symmetric(vertical: 15),
        border: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: GaraColors.studentBorder)),
        enabledBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: GaraColors.studentBorder)),
        focusedBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide:
                const BorderSide(color: GaraColors.studentPrimary, width: 1.5)),
        errorBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide: const BorderSide(color: Colors.redAccent)),
        focusedErrorBorder: OutlineInputBorder(
            borderRadius: baseRadius,
            borderSide:
                const BorderSide(color: Colors.redAccent, width: 1.5)),
        errorStyle: tt.bodySmall?.copyWith(
            color: Colors.redAccent, fontSize: 11),
      ),
    );
  }
}
