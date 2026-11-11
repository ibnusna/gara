import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../utils/app_constants.dart';
import '../gara_primary_button.dart';
import 'login_input_field.dart';

class LoginScreenContent extends StatelessWidget {
  final Animation<double> fadeAnim;
  final TextEditingController identifierCtrl;
  final TextEditingController passwordCtrl;
  final FocusNode identifierFocus;
  final FocusNode passwordFocus;
  final GlobalKey<FormState> formKey;
  final bool isPasswordVisible;
  final bool isLoading;
  final VoidCallback onTogglePassword;
  final VoidCallback onSubmit;
  final VoidCallback onForgotPassword;

  const LoginScreenContent({
    super.key,
    required this.fadeAnim,
    required this.identifierCtrl,
    required this.passwordCtrl,
    required this.identifierFocus,
    required this.passwordFocus,
    required this.formKey,
    required this.isPasswordVisible,
    required this.isLoading,
    required this.onTogglePassword,
    required this.onSubmit,
    required this.onForgotPassword,
  });

  @override
  Widget build(BuildContext context) {
    final tt = Theme.of(context).textTheme;

    return Center(
      child: SingleChildScrollView(
        physics: const ClampingScrollPhysics(),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 400),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 28),
            child: FadeTransition(
              opacity: fadeAnim,
              child: Form(
                key: formKey,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 20),
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.08),
                            blurRadius: 20,
                            spreadRadius: 1,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(20),
                        child: Image.asset(
                          'launchericon-512x512.png',
                          height: 76,
                          width: 76,
                          fit: BoxFit.contain,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'GARA',
                      style: tt.headlineSmall?.copyWith(
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF1E293B),
                        letterSpacing: 3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Masukkan kredensial untuk melanjutkan',
                      style: tt.bodySmall?.copyWith(color: GaraColors.studentTextMuted),
                    ),
                    const SizedBox(height: 36),
                    LoginInputField(
                      controller: identifierCtrl,
                      focusNode: identifierFocus,
                      nextFocus: passwordFocus,
                      icon: Icons.person_outline_rounded,
                      placeholder: 'Username / NIS',
                      isPassword: false,
                      isPasswordVisible: false,
                      onTogglePassword: onTogglePassword,
                    ),
                    const SizedBox(height: 16),
                    LoginInputField(
                      controller: passwordCtrl,
                      focusNode: passwordFocus,
                      icon: Icons.lock_outline_rounded,
                      placeholder: 'Password',
                      isPassword: true,
                      isPasswordVisible: isPasswordVisible,
                      onTogglePassword: onTogglePassword,
                      onSubmit: onSubmit,
                    ),
                    const SizedBox(height: 28),
                    GaraPrimaryButton(
                      label: 'LOGIN',
                      onPressed: onSubmit,
                      isLoading: isLoading,
                    ),
                    const SizedBox(height: 12),
                    TextButton(
                      onPressed: onForgotPassword,
                      style: TextButton.styleFrom(
                        foregroundColor: GaraColors.studentPrimary,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      ),
                      child: Text(
                        'Lupa Password?',
                        style: GoogleFonts.poppins(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: GaraColors.studentPrimary,
                        ),
                      ),
                    ),
                    const SizedBox(height: 28),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
