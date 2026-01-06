import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AvailabilitySlot {
  int dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
  TimeOfDay startTime;
  TimeOfDay endTime;

  AvailabilitySlot({
    required this.dayOfWeek,
    required this.startTime,
    required this.endTime,
  });

  factory AvailabilitySlot.fromJson(Map<String, dynamic> json) {
    return AvailabilitySlot(
      dayOfWeek: json['day_of_week'],
      startTime: TimeOfDay(
        hour: int.parse(json['start_time'].split(':')[0]),
        minute: int.parse(json['start_time'].split(':')[1]),
      ),
      endTime: TimeOfDay(
        hour: int.parse(json['end_time'].split(':')[0]),
        minute: int.parse(json['end_time'].split(':')[1]),
      ),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'day_of_week': dayOfWeek,
      'start_time':
          '${startTime.hour.toString().padLeft(2, '0')}:${startTime.minute.toString().padLeft(2, '0')}',
      'end_time':
          '${endTime.hour.toString().padLeft(2, '0')}:${endTime.minute.toString().padLeft(2, '0')}',
    };
  }
}
// End of Mocks

class BarberAvailabilityScreen extends StatefulWidget {
  final String barberId;
  final String barberName;
  final ApiService apiService = ApiService();

  BarberAvailabilityScreen({
    required this.barberId,
    required this.barberName,
    Key? key,
  }) : super(key: key);

  @override
  _BarberAvailabilityScreenState createState() =>
      _BarberAvailabilityScreenState();
}

class _BarberAvailabilityScreenState extends State<BarberAvailabilityScreen> {
  late Future<List<AvailabilitySlot>> _availabilityFuture;
  Map<int, List<AvailabilitySlot>> _slotsByDay = {};
  final List<String> _daysOfWeek = [
    'Domingo',
    'Segunda',
    'Terça',
    'Quarta',
    'Quinta',
    'Sexta',
    'Sábado',
  ];

  @override
  void initState() {
    super.initState();
    _availabilityFuture = widget.apiService
        .getBarberAvailability(widget.barberId)
        .then((slots) {
          final slotObjs = (slots as List)
              .map((json) => AvailabilitySlot.fromJson(json))
              .toList();
          _groupSlotsByDay(slotObjs);
          return slotObjs;
        });
  }

  void _groupSlotsByDay(List<AvailabilitySlot> slots) {
    _slotsByDay = {for (var i = 0; i < 7; i++) i: []};
    for (var slot in slots) {
      _slotsByDay[slot.dayOfWeek]?.add(slot);
    }
  }

  Future<void> _selectTime(
    BuildContext context,
    AvailabilitySlot slot,
    bool isStartTime,
  ) async {
    final TimeOfDay? picked = await showTimePicker(
      context: context,
      initialTime: isStartTime ? slot.startTime : slot.endTime,
    );
    if (picked != null) {
      setState(() {
        if (isStartTime) {
          slot.startTime = picked;
        } else {
          slot.endTime = picked;
        }
      });
    }
  }

  void _addSlot(int dayOfWeek) {
    setState(() {
      _slotsByDay[dayOfWeek]?.add(
        AvailabilitySlot(
          dayOfWeek: dayOfWeek,
          startTime: TimeOfDay(hour: 9, minute: 0),
          endTime: TimeOfDay(hour: 18, minute: 0),
        ),
      );
    });
  }

  void _removeSlot(int dayOfWeek, AvailabilitySlot slot) {
    setState(() {
      _slotsByDay[dayOfWeek]?.remove(slot);
    });
  }

  void _saveAvailability() async {
    try {
      List<AvailabilitySlot> allSlots = _slotsByDay.values
          .expand((slots) => slots)
          .toList();
      await widget.apiService.updateBarberAvailability(
        widget.barberId,
        allSlots.map((slot) => slot.toJson()).toList(),
      );
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Disponibilidade salva com sucesso!')),
      );
    } catch (e) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Erro ao salvar: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Disponibilidade de ${widget.barberName}'),
        actions: [
          IconButton(
            icon: Icon(Icons.save),
            onPressed: _saveAvailability,
            tooltip: 'Salvar',
          ),
        ],
      ),
      body: FutureBuilder<List<AvailabilitySlot>>(
        future: _availabilityFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text("Erro ao carregar disponibilidade."));
          } else {
            return ListView.builder(
              itemCount: _daysOfWeek.length,
              itemBuilder: (context, index) {
                int dayOfWeek =
                    (index + 1) %
                    7; // Adjust to match backend (0=Sun, 1=Mon...)
                String dayName = _daysOfWeek[dayOfWeek];
                List<AvailabilitySlot> daySlots = _slotsByDay[dayOfWeek] ?? [];

                return Card(
                  margin: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  child: Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          dayName,
                          style: Theme.of(context).textTheme.titleLarge,
                        ),
                        SizedBox(height: 8),
                        ...daySlots
                            .map((slot) => _buildSlotEditor(slot, dayOfWeek))
                            .toList(),
                        TextButton.icon(
                          icon: Icon(Icons.add),
                          label: Text('Adicionar intervalo'),
                          onPressed: () => _addSlot(dayOfWeek),
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          }
        },
      ),
    );
  }

  Widget _buildSlotEditor(AvailabilitySlot slot, int dayOfWeek) {
    return Row(
      children: [
        Expanded(
          child: InkWell(
            onTap: () => _selectTime(context, slot, true),
            child: Text(slot.startTime.format(context)),
          ),
        ),
        Text(' às '),
        Expanded(
          child: InkWell(
            onTap: () => _selectTime(context, slot, false),
            child: Text(slot.endTime.format(context)),
          ),
        ),
        IconButton(
          icon: Icon(Icons.delete, color: Colors.red),
          onPressed: () => _removeSlot(dayOfWeek, slot),
        ),
      ],
    );
  }
}
