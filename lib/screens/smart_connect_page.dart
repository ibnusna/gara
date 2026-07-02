import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../utils/app_constants.dart';
import '../widgets/hybrid_wrapper.dart';

class SmartConnectPage extends StatefulWidget {
  const SmartConnectPage({super.key});

  @override
  State<SmartConnectPage> createState() => _SmartConnectPageState();
}

class _SmartConnectPageState extends State<SmartConnectPage> {
  final _ipController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  void _connect() {
    if (_formKey.currentState!.validate()) {
      HapticFeedback.mediumImpact();
      String url = _ipController.text.trim();
      if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'http://$url'; // Default to http if no protocol is given
      }

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (context) => HybridWrapper(
            url: url,
            pageTitle: 'Smart Connect',
          ),
        ),
      );
    }
  }

  @override
  void dispose() {
    _ipController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.dsBgBody,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: GaraColors.dsSlate800),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 20),
                Text(
                  'SmartConnect:',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 28,
                    fontWeight: FontWeight.w800,
                    color: GaraColors.dsSlate800,
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  'Masukkan alamat IP server lokal (Local Server) untuk memanggil fitur webview seperti ruang belajar secara mandiri.',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 14,
                    color: GaraColors.dsSlate500,
                    height: 1.5,
                  ),
                ),
                const SizedBox(height: 40),
                TextFormField(
                  controller: _ipController,
                  style: GoogleFonts.plusJakartaSans(color: GaraColors.dsSlate800),
                  decoration: InputDecoration(
                    hintText: 'Contoh: 192.168.1.100 atau http://domain.com',
                    hintStyle: TextStyle(color: GaraColors.dsSlate400),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide(color: GaraColors.dsSlate200),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide(color: GaraColors.dsSlate200),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: const BorderSide(color: GaraColors.dsPrimaryBright, width: 2),
                    ),
                    prefixIcon: const Icon(Icons.language_rounded, color: GaraColors.dsSlate400),
                  ),
                  validator: (val) {
                    if (val == null || val.trim().isEmpty) {
                      return 'Alamat IP / URL tidak boleh kosong';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  height: 56,
                  child: ElevatedButton(
                    onPressed: _connect,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: GaraColors.dsPrimaryDeep,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 0,
                    ),
                    child: Text(
                      'Masuk Webview',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
