from pymongo import MongoClient
import sys

# Обеспечиваем корректный вывод кириллицы в консоли Windows
if sys.platform == "win32":
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# Подключение к локальной базе данных MongoDB
client = MongoClient("mongodb://localhost:27017/")
db = client["university_db"]
students_col = db["students_ranking"]

def seed_initial_students():
    """Автоматическое добавление 5 тестовых студентов с баллами за СРО"""
    sample_students = [
        {
            "name": "Абзал Абдусаттар",
            "group": "ИС-24-22",
            "sro_points": 95,
            "tasks_completed": ["СРО №1 (NoSQL MongoDB)", "СРО №2 (PHP Wizard & Session)"]
        },
        {
            "name": "Алихан Смаилов",
            "group": "ИС-24-22",
            "sro_points": 88,
            "tasks_completed": ["СРО №1 (NoSQL MongoDB)", "Лабораторная работа №3"]
        },
        {
            "name": "Мадина Серикова",
            "group": "БТ-24-11",
            "sro_points": 90,
            "tasks_completed": ["СРО №1 (NoSQL MongoDB)", "Презентация СРО"]
        },
        {
            "name": "Данияр Ахметов",
            "group": "ИС-24-22",
            "sro_points": 75,
            "tasks_completed": ["СРО №1 (NoSQL MongoDB)"]
        },
        {
            "name": "Аружан Нурланова",
            "group": "БТ-24-11",
            "sro_points": 82,
            "tasks_completed": ["СРО №1 (NoSQL MongoDB)", "Практическое задание"]
        }
    ]

    added_count = 0
    for student in sample_students:
        # Проверяем, чтобы не дублировать, если они уже внесены
        if not students_col.find_one({"name": student["name"], "group": student["group"]}):
            students_col.insert_one(student)
            added_count += 1

    if added_count > 0:
        print(f"\n[Успех] В базу успешно добавлено {added_count} новых студентов!")
    else:
        print("\n[Инфо] Все 5 студентов уже присутствуют в базе данных MongoDB.")

def add_student():
    print("\n--- Регистрация нового студента ---")
    name = input("Введите ФИО студента: ").strip()
    group = input("Введите группу (например, ИС-24-22): ").strip()

    if not name or not group:
        print("Ошибка: поля не должны быть пустыми!")
        return

    existing = students_col.find_one({"name": name, "group": group})
    if existing:
        print(f"Студент {name} из группы {group} уже зарегистрирован!")
        return

    student_doc = {
        "name": name,
        "group": group,
        "sro_points": 0,
        "tasks_completed": []
    }

    students_col.insert_one(student_doc)
    print(f"Студент {name} успешно добавлен в базу данных MongoDB!")

def add_sro_points():
    print("\n--- Начисление баллов за СРО ---")
    name = input("Введите ФИО студента для поиска: ").strip()
    student = students_col.find_one({"name": {"$regex": name, "$options": "i"}})

    if not student:
        print("Студент не найден!")
        return

    print(f"Найден студент: {student['name']} (Группа: {student['group']}, Текущие баллы: {student['sro_points']})")

    try:
        points = int(input("Введите количество баллов за СРО (например, 15): "))
        task_name = input("Название выполненного задания (например, СРО по PHP/NoSQL): ").strip()
    except ValueError:
        print("Ошибка: введите число для баллов!")
        return

    students_col.update_one(
        {"_id": student["_id"]},
        {
            "$inc": {"sro_points": points},
            "$push": {"tasks_completed": task_name}
        }
    )
    print(f"Успешно начислено {points} баллов студенту {student['name']}!")

def show_leaderboard():
    print("\n--- Общий рейтинг студентов (Leaderboard СРО) ---")
    cursor = students_col.find().sort("sro_points", -1)

    found = False
    for idx, student in enumerate(cursor, 1):
        found = True
        print(f"{idx}. {student['name']} (Группа: {student.get('group', 'Н/У')}) — Баллы СРО: {student['sro_points']}")
        tasks = student.get('tasks_completed', [])
        if tasks:
            print(f"   Выполненные задания: {', '.join(tasks)}")

    if not found:
        print("База данных пока пуста. Загрузите тестовых студентов или добавьте вручную!")

def main_menu():
    while True:
        print("\n========================================")
        print("  СИСТЕМА РЕЙТИНГА СТУДЕНТОВ (NoSQL СРО)")
        print("========================================")
        print("1. Зарегистрировать студента")
        print("2. Начислить баллы за СРО задание")
        print("3. Показать общий рейтинг (Leaderboard)")
        print("4. Автоматически заполнить базу (5 студентов)")
        print("5. Выход")

        choice = input("Выберите пункт меню (1-5): ").strip()

        if choice == "1":
            add_student()
        elif choice == "2":
            add_sro_points()
        elif choice == "3":
            show_leaderboard()
        elif choice == "4":
            seed_initial_students()
        elif choice == "5":
            print("Выход из программы. Успешной защиты СРО!")
            break
        else:
            print("Неверный выбор, попробуйте снова от 1 до 5.")

if __name__ == "__main__":
    main_menu()
