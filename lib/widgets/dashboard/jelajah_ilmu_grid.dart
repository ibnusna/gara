






















import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../screens/in_app_browser_page.dart';
import '../../utils/app_constants.dart';
import 'ds_glass_card.dart';

class _ExtLink {
  final String title;
  final String url;
  final IconData icon;
  final Color color;
  const _ExtLink({required this.title, required this.url, required this.icon, required this.color});
}


const _links = [
  _ExtLink(title: 'Bank Soal Kemdikbud', url: 'https://rumah.pendidikan.go.id/ruang/murid', icon: Icons.account_balance_rounded, color: Color(0xFF2563EB)),
  _ExtLink(title: 'E-Book',              url: 'https://buku.kemendikdasmen.go.id/',          icon: Icons.import_contacts_rounded,   color: Color(0xFF16A34A)),
  _ExtLink(title: 'Jurnal',              url: 'https://scholar.google.co.id/',               icon: Icons.science_rounded,            color: Color(0xFF4285F4)),
  _ExtLink(title: 'Games',              url: 'https://www.education.com/resources/games/',   icon: Icons.sports_esports_rounded,     color: Color(0xFFDC2626)),
  _ExtLink(title: 'KBBI',               url: 'https://kbbi.web.id/',                        icon: Icons.book_rounded,               color: Color(0xFFD97706)),
  _ExtLink(title: 'Perpusnas',          url: 'https://www.perpusnas.go.id/',                icon: Icons.local_library_rounded,      color: Color(0xFF9333EA)),
];

class JelajahIlmuGrid extends StatelessWidget {
  const JelajahIlmuGrid({super.key});

  void _open(BuildContext context, String url, String title) {
    HapticFeedback.lightImpact();
    
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => InAppBrowserPage(
          title: title,
          initialUrl: url,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        
        
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 12),
          child: Text(
            'Jelajah Ilmu',
            style: GoogleFonts.plusJakartaSans(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: GaraColors.dsSlate800,
              letterSpacing: 0.2,
            ),
          ),
        ),

        GridView.count(
          crossAxisCount: 3,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
          childAspectRatio: 1.0,
          children: _links
              .map((l) => _LinkCard(link: l, onTap: () => _open(context, l.url, l.title)))
              .toList(),
        ),
      ],
    );
  }
}

class _LinkCard extends StatefulWidget {
  final _ExtLink link;
  final VoidCallback onTap;
  const _LinkCard({required this.link, required this.onTap});

  @override
  State<_LinkCard> createState() => _LinkCardState();
}

class _LinkCardState extends State<_LinkCard> {
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) {
        setState(() => _pressed = false);
        HapticFeedback.lightImpact();
        widget.onTap();
      },
      onTapCancel: () => setState(() => _pressed = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 120),
        transform: Matrix4.identity()..scale(_pressed ? 0.95 : 1.0),
        transformAlignment: Alignment.center,
        
        
        child: GaraGlassCard(
          borderRadius: BorderRadius.circular(20),
          child: Stack(
            children: [
              
              if (_pressed)
                Container(
                  decoration: BoxDecoration(
                    color: widget.link.color.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(20),
                  ),
                ),
              Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(widget.link.icon, color: widget.link.color, size: 28),
                    const SizedBox(height: 6),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 6),
                      child: Text(
                        widget.link.title,
                        textAlign: TextAlign.center,
                        maxLines: 2,
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: GaraColors.dsSlate800,
                          height: 1.3,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
