import time
from datetime import datetime
from locust import HttpUser, task, between

class StafGudang(HttpUser):
    # Simulasi jeda waktu staf saat bekerja di gudang
    wait_time = between(2, 5)
    token = ""

    def on_start(self):
        headers = {
            "Accept": "application/json"
        }
        
        response = self.client.post("/api/login", json={
            "email": "staff@sigmastationery.com", 
            "password": "sigma123"
        }, headers=headers)
        
        if response.status_code == 200:
            self.token = response.json().get("access_token")    

    @task(3)
    def pull_tugas(self):
        if not self.token:
            return
            
        headers = {
            "Authorization": f"Bearer {self.token}",
            "Accept": "application/json"
        }
        self.client.get("/api/sync/pull", headers=headers, name="1. API Pull Jadwal")

    @task(1)
    def push_hasil(self):
        if not self.token:
            return

        headers = {
            "Authorization": f"Bearer {self.token}",
            "Accept": "application/json"
        }
        
        waktu_sekarang = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        payload = {
            "cycle_counts": [
                {
                    "id": 60, 
                    "rack_id": 10,
                    "started_at": waktu_sekarang,
                    "finished_at": waktu_sekarang,
                    "details": [
                        {
                            "item_id": 80, 
                            "physical_stock": 100
                        }
                    ]
                }
            ]
        }

        response = self.client.post("/api/sync/push", json=payload, headers=headers, name="2. API Push Hasil")
        
        if response.status_code == 500:
            print(f"Tertangkap Error 500! Pesan dari server: {response.text[:500]}") 