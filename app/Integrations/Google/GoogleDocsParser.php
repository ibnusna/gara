<?php

namespace App\Integrations\Google;

use Google\Client;
use Google\Service\Drive;

class GoogleDocsParser
{
    protected GoogleTokenManager $tokenManager;

    public function __construct()
    {
        $this->tokenManager = new GoogleTokenManager();
    }

    public function extractQuestionsFromDoc(int $userId, string $docId): array
    {
        $accessToken = $this->tokenManager->getValidAccessToken($userId);

        if (!$accessToken) {
            return ['error' => 'Akun Google belum terhubung atau token tidak valid.'];
        }

        $client = new Client();
        $client->setAccessToken(['access_token' => $accessToken, 'token_type' => 'Bearer']);

        $driveService = new Drive($client);

        $response = $driveService->files->export($docId, 'text/plain', ['alt' => 'media']);
        $rawText = (string) $response->getBody();

        if (empty(trim($rawText))) {
            return ['error' => 'Dokumen kosong atau tidak dapat diakses.'];
        }

        $formattedText = $this->formatRawText($rawText);

        return [
            'raw_text' => $formattedText,
            'parsed' => $this->parseQuestionsFromText($formattedText)
        ];
    }

    public function formatRawText(string $text): string
    {
        
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        
        $text = preg_replace('/(?<!\n)[ \t]+(?=[A-E][\.\)]\s)/ui', "\n", $text);
        
        $text = preg_replace('/(?<!\n)[ \t]+(?=Kunci\s+Jawaban[\s:]+)/ui', "\n", $text);
        $text = preg_replace('/(?<!\n)[ \t]+(?=Kunci[\s:]+(?!\s*Jawaban))/ui', "\n", $text);
        $text = preg_replace('/(?<!\n)[ \t]+(?=(?<!Kunci\s)Jawaban[\s:]+)/ui', "\n", $text);

        
        $text = preg_replace('/\n{3,}/u', "\n\n", $text);

        return trim($text);
    }

    protected function parseQuestionsFromText(string $text): array
    {
        if (empty(trim($text))) {
            return [];
        }

        
        $questionBlocks = preg_split('/^\s*\d+[\.\)]\s/m', $text);
        
        
        $questionBlocks = array_filter(array_map('trim', $questionBlocks));
        $questionBlocks = array_values($questionBlocks);

        $questions = [];
        $index = 0;

        foreach ($questionBlocks as $block) {
            $type = "PG";
            $soalText = "";
            $kunci = "";
            $options = ['A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => ''];

            if (stripos($block, '[TIPE:BENAR-SALAH]') !== false) {
                $type = "BENAR_SALAH";
                $block = preg_replace('/\[TIPE:BENAR-SALAH\]/i', '', $block);
                $block = trim($block);
            }

            $lines = explode("\n", $block);
            $lines = array_map('trim', $lines);
            
            $soalText = array_shift($lines);
            if ($soalText === null) {
                $soalText = '';
            }

            $soalTextUpper = strtoupper($soalText);
            if ($type === "BENAR_SALAH" && (
                strpos($soalTextUpper, ': BENAR') !== false || 
                strpos($soalTextUpper, ': SALAH') !== false || 
                strpos($soalTextUpper, ':BENAR') !== false || 
                strpos($soalTextUpper, ':SALAH') !== false
            )) {
                array_unshift($lines, $soalText);
                $soalText = "Tentukan Benar/Salah dari pernyataan berikut!";
            }

            $bsKeys = [];
            $bsIndex = 0;
            $mapChar = ["A", "B", "C", "D", "E"];

            foreach ($lines as $line) {
                $t = trim($line);
                if ($t === '') {
                    continue;
                }

                if ($type === "BENAR_SALAH" && stripos($t, 'kunci jawaban:') === 0) {
                    continue;
                }

                if ($type === "BENAR_SALAH") {
                    $parts = explode(":", $t, 2);
                    if (count($parts) >= 2) {
                        $statement = trim($parts[0]);
                        $answer = strtoupper(trim($parts[1]));
                        if ($bsIndex < 5) {
                            $options[$mapChar[$bsIndex]] = $statement;
                            $bsKeys[] = $answer;
                            $bsIndex++;
                        }
                    }
                } else {
                    if (preg_match('/^(?:Kunci\s+Jawaban|Kunci|Jawaban)[\s:]+(.*)/ui', $t, $keyMatch)) {
                        $kunci = trim($keyMatch[1]);
                    } elseif (preg_match_all('/(?<=^|\s)([A-E])[\.\)]\s*(.*?)(?=\s+(?:[A-E][\.\)]|$)|$)/i', $t, $optMatches, PREG_SET_ORDER)) {
                        foreach ($optMatches as $optMatch) {
                            $options[strtoupper($optMatch[1])] = trim($optMatch[2]);
                        }
                    }
                }
            }

            if ($type === "BENAR_SALAH") {
                $kunci = implode(",", $bsKeys);
            } else {
                $hasOptions = false;
                foreach ($options as $val) {
                    if ($val !== '') {
                        $hasOptions = true;
                        break;
                    }
                }

                if ($kunci !== '' && !$hasOptions) {
                    $type = "ISIAN";
                } elseif (strpos($kunci, ',') !== false) {
                    $type = "PG_KOMPLEKS";
                    $keys = explode(',', $kunci);
                    $keys = array_map(function($k) {
                        return strtoupper(trim($k));
                    }, $keys);
                    $kunci = implode(',', $keys);
                } else {
                    $kunci = strtoupper($kunci);
                }
            }

            $questions[] = [
                'no'    => $index + 1,
                'tipe'  => $type,
                'soal'  => $soalText,
                'a'     => $options['A'],
                'b'     => $options['B'],
                'c'     => $options['C'],
                'd'     => $options['D'],
                'e'     => $options['E'],
                'kunci' => $kunci,
            ];
            $index++;
        }

        return $questions;
    }
}
