#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP32Servo.h> // Servo
#include <WiFi.h>
#include <HTTPClient.h>

// ======================[ Konfigurasi Pin & Objek ]======================
#define SS_PIN 5
#define RST_PIN 26
MFRC522 rfid(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// HC-SR04 - 3 sensor
const int trigPins[3] = {12, 25, 27};  
const int echoPins[3] = {13, 35, 34};  
const long ambangBatasi = 10;          // Ambang jarak dalam cm
bool slotStatus[3];                    // Status 3 slot

// RFID yang diizinkan
String allowedTags[] = {
  "21ED352",
  "A2DB01B",
  "2D1CE1"
};

// Servo
Servo palang;
const int servoPin = 14;
const int posisiTutup = 0;
const int posisiBuka = 90;

// Wi-Fi
const char* ssid = "Your_SSID";        
const char* password = "Your_Password"; 

// ====================[ Deklarasi Fungsi ]===================
void cekSemuaSlot();
void tampilkanSlotDiLCD();
long bacaJarak(int trigPin, int echoPin);
void sendSlotStatusToServer();

// ====================[ Setup ]===================
void setup() {
  Serial.begin(115200);
  SPI.begin();
  rfid.PCD_Init();

  lcd.init();
  lcd.backlight();

  // Inisialisasi pin TRIG/ECHO untuk 3 sensor
  for (int i = 0; i < 3; i++) {
    pinMode(trigPins[i], OUTPUT);
    pinMode(echoPins[i], INPUT);
  }

  // Inisialisasi servo untuk palang
  palang.attach(servoPin);
  palang.write(posisiTutup); // Mulai tertutup

  lcd.clear();
  lcd.print("Parkir Siap...");
  delay(1000);

  // Koneksi ke Wi-Fi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

// ====================[ Loop ]===================
void loop() {
  // Baca kartu RFID
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    String rfidTag = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
      rfidTag += String(rfid.uid.uidByte[i], HEX);  // Tanpa leading zero
    }
    rfidTag.toUpperCase();

    Serial.print("Tag dibaca: ");
    Serial.println(rfidTag);

    bool diizinkan = false;
    for (String tag : allowedTags) {
      if (rfidTag == tag) {
        diizinkan = true;
        break;
      }
    }

    lcd.clear();
    if (diizinkan) {
      lcd.print("Akses Diterima");
      Serial.println("Akses Diterima");

      // Buka palang
      palang.write(posisiBuka);
      delay(3000); // Waktu palang terbuka
      palang.write(posisiTutup);
    } else {
      lcd.print("Akses Ditolak");
      Serial.println("Akses Ditolak");
    }

    delay(2000);
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
  }

  // Update status parkir
  cekSemuaSlot();
  tampilkanSlotDiLCD();
  
  // Kirim data status slot ke server
  sendSlotStatusToServer();

  delay(1000);
}

// ====================[ Fungsi Bantu ]===================
long bacaJarak(int trigPin, int echoPin) {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);

  long durasi = pulseIn(echoPin, HIGH, 30000);
  long jarak = durasi * 0.034 / 2;
  if (jarak == 0) jarak = 300;
  return jarak;
}

void cekSemuaSlot() {
  for (int i = 0; i < 3; i++) {
    long jarak = bacaJarak(trigPins[i], echoPins[i]);
    slotStatus[i] = jarak < ambangBatasi;
    Serial.print("Slot ");
    Serial.print(i + 1);
    Serial.print(": ");
    Serial.print(jarak);
    Serial.println(" cm");
  }
}

void tampilkanSlotDiLCD() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("S1:");
  lcd.print(slotStatus[0] ? "Full" : "Empty");

  lcd.setCursor(9, 0);
  lcd.print("S2:");
  lcd.print(slotStatus[1] ? "Full" : "Empty");

  lcd.setCursor(0, 1);
  lcd.print("S3:");
  lcd.print(slotStatus[2] ? "Full" : "Empty");

  int sisa = 0;
  for (int i = 0; i < 3; i++) {
    if (!slotStatus[i]) sisa++;
  }

  lcd.setCursor(9, 1);
  lcd.print("Left:");
  lcd.print(sisa);
}

// Fungsi untuk mengirim data status slot ke server
void sendSlotStatusToServer() {
  HTTPClient http;
  String serverURL = "Your_IP_Address_Ngrok/api"; // Ganti dengan URL server Anda

  // Data JSON untuk mengirim status slot parkir
  String payload = "{\"slot1\": " + String(slotStatus[0]) + ", \"slot2\": " + String(slotStatus[1]) + ", \"slot3\": " + String(slotStatus[2]) + "}";

  http.begin(serverURL); 
  http.addHeader("Content-Type", "application/json"); 

  int httpResponseCode = http.POST(payload); 

  if (httpResponseCode > 0) {
    Serial.println("Data terkirim dengan status: " + String(httpResponseCode));
  } else {
    Serial.println("Gagal mengirim data. Kode error: " + String(httpResponseCode));
  }

  http.end(); 
}
