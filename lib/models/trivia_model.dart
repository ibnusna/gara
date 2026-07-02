// Model data untuk satu soal trivia Brain Warmup
class TriviaQuestion {
  final String pertanyaan;
  final List<String> pilihan;
  final int jawabanBenar;

  const TriviaQuestion({
    required this.pertanyaan,
    required this.pilihan,
    required this.jawabanBenar,
  });
}

// Dummy bank soal — FASE 2: ganti dengan API publik trivia
const List<TriviaQuestion> triviaBank = [
  TriviaQuestion(
    pertanyaan: 'Planet manakah yang dikenal sebagai "Planet Merah"?',
    pilihan: ['Venus', 'Jupiter', 'Mars', 'Saturnus'],
    jawabanBenar: 2,
  ),
  TriviaQuestion(
    pertanyaan: 'Berapakah hasil akar kuadrat dari 144?',
    pilihan: ['10', '12', '14', '16'],
    jawabanBenar: 1,
  ),
  TriviaQuestion(
    pertanyaan: 'Siapakah penemu teori relativitas khusus?',
    pilihan: ['Newton', 'Einstein', 'Bohr', 'Tesla'],
    jawabanBenar: 1,
  ),
  TriviaQuestion(
    pertanyaan: 'Unsur kimia apa yang memiliki simbol "Au"?',
    pilihan: ['Perak', 'Aluminium', 'Emas', 'Tembaga'],
    jawabanBenar: 2,
  ),
];
