import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import '../theme/app_colors.dart';
import '../services/api_service.dart';

class ReportsScreen extends StatefulWidget {
  const ReportsScreen({super.key});

  @override
  State<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends State<ReportsScreen> {
  final ApiService _api = ApiService();
  Map<String, dynamic> _reports = {};
  bool _isLoading = true;
  String? _errorMessage;
  DateTimeRange? _selectedRange;

  @override
  void initState() {
    super.initState();
    _selectedRange = DateTimeRange(
      start: DateTime(DateTime.now().year, DateTime.now().month, 1),
      end: DateTime.now(),
    );
    _loadReports();
  }

  Future<void> _loadReports() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final start = _selectedRange!.start;
      final end = _selectedRange!.end;
      final startStr =
          '${start.year}-${start.month.toString().padLeft(2, '0')}-${start.day.toString().padLeft(2, '0')}';
      final endStr =
          '${end.year}-${end.month.toString().padLeft(2, '0')}-${end.day.toString().padLeft(2, '0')}';

      final data = await _api.getReports(startStr, endStr);
      setState(() {
        _reports = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorMessage = 'Erro ao carregar relatórios: ${e.toString()}';
      });
    }
  }

  Future<void> _selectDateRange() async {
    final range = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      initialDateRange: _selectedRange,
    );

    if (range != null) {
      setState(() => _selectedRange = range);
      _loadReports();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Relatórios'),
        actions: [
          IconButton(
            icon: const Icon(Icons.date_range),
            onPressed: _selectDateRange,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.error_outline,
                    size: 64,
                    color: AppColors.error,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    _errorMessage!,
                    style: const TextStyle(color: AppColors.textSecondary),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    onPressed: _loadReports,
                    icon: const Icon(Icons.refresh),
                    label: const Text('Tentar Novamente'),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadReports,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Period selector
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Período:',
                                  style: TextStyle(
                                    color: AppColors.textSecondary,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  '${_selectedRange!.start.day}/${_selectedRange!.start.month} - ${_selectedRange!.end.day}/${_selectedRange!.end.month}',
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                            ElevatedButton.icon(
                              onPressed: _selectDateRange,
                              icon: const Icon(Icons.calendar_today),
                              label: const Text('Alterar'),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Revenue card
                    Card(
                      color: AppColors.primaryGold.withOpacity(0.1),
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: AppColors.primaryGold,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Icon(Icons.account_balance_wallet, color: Colors.white, size: 32),
                                ),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text('Receita Líquida', style: TextStyle(fontSize: 16, color: AppColors.textSecondary)),
                                      const SizedBox(height: 4),
                                      Text(
                                        'R\$ ${_parseAmount(_reports['summary']?['total_net_revenue']).toStringAsFixed(2).replaceAll('.', ',')}',
                                        style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: AppColors.primaryGold),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 16),
                            const Divider(),
                            const SizedBox(height: 8),
                             _buildRevenueDetail(
                              "Receita Bruta:",
                              _parseAmount(_reports['summary']?['total_gross_revenue']),
                              positive: true,
                            ),
                            _buildRevenueDetail(
                              "Taxas:",
                              _parseAmount(_reports['summary']?['adjustments']?['fee']),
                              positive: false,
                            ),
                            _buildRevenueDetail(
                              "Descontos:",
                              _parseAmount(_reports['summary']?['adjustments']?['discount']),
                              positive: false,
                            ),
                             _buildRevenueDetail(
                              "Bônus:",
                              _parseAmount(_reports['summary']?['adjustments']?['bonus']),
                              positive: true,
                            ),
                            const Divider(),
                            const SizedBox(height: 8),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceAround,
                              children: [
                                Column(
                                  children: [
                                    const Text('Agend. Concluídos', style: TextStyle(color: AppColors.textSecondary)),
                                    Text(
                                      '${_reports['summary']?['total_completed_appointments'] ?? 0}',
                                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                                Container(width: 1, height: 40, color: AppColors.textSecondary.withOpacity(0.5)),
                                Column(
                                  children: [
                                    const Text('Ticket Médio', style: TextStyle(color: AppColors.textSecondary)),
                                    Text(
                                      'R\$ ${_calculateAverage()}',
                                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Revenue chart
                    const Text(
                      'Desempenho Financeiro',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: SizedBox(
                          height: 200,
                          child: _buildRevenueChart(),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Top services
                    const Text(
                      'Serviços Mais Vendidos',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    ...(_reports['topServices'] as List? ?? []).map((service) {
                      return Card(
                        margin: const EdgeInsets.only(bottom: 8),
                        child: ListTile(
                          leading: const CircleAvatar(
                            backgroundColor: AppColors.primaryGold,
                            child: Icon(Icons.cut, color: AppColors.black),
                          ),
                          title: Text(service['name']),
                          subtitle: Text(
                            'R\$ ${_parseAmount(service['revenue']).toStringAsFixed(2).replaceAll('.', ',')}',
                          ),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.info.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              '${service['count']}x',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      );
                    }),

                    const SizedBox(height: 24),

                    // Payment methods chart
                    const Text(
                      'Distribuição de Pagamentos',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: SizedBox(
                          height: 250,
                          child: _buildPaymentMethodsChart(),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Payment methods
                    const Text(
                      'Formas de Pagamento',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    ...(_reports['paymentMethods'] as List? ?? []).map((
                      method,
                    ) {
                      return Card(
                        margin: const EdgeInsets.only(bottom: 8),
                        child: ListTile(
                          leading: Icon(
                            _getPaymentIcon(method['payment_method']),
                            color: AppColors.primaryGold,
                          ),
                          title: Text(
                            _getPaymentName(method['payment_method']),
                          ),
                          subtitle: Text('${method['count']} pagamentos'),
                          trailing: Text(
                            'R\$ ${_parseAmount(method['total']).toStringAsFixed(2).replaceAll('.', ',')}',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      );
                    }),
                  ],
                ),
              ),
            ),
    );
  }

  double _parseAmount(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) {
      try {
        return double.parse(value);
      } catch (e) {
        return 0.0;
      }
    }
    return 0.0;
  }

  String _calculateAverage() {
    final total = _parseAmount(_reports['summary']?['total_net_revenue']);
    final count = _parseAmount(_reports['summary']?['total_completed_appointments']);
    final avg = count > 0 ? total / count : 0;
    return avg.toStringAsFixed(2).replaceAll('.', ',');
  }

  Widget _buildRevenueDetail(String label, double value, {required bool positive}) {
    final sign = positive ? '+' : '-';
    final color = positive ? AppColors.success : AppColors.error;
    if (value == 0 && !positive) return SizedBox.shrink(); // Do not show negative zero

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: AppColors.textSecondary)),
          Text(
            '$sign R\$ ${value.toStringAsFixed(2).replaceAll('.', ',')}',
            style: TextStyle(
              fontWeight: FontWeight.w500,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  IconData _getPaymentIcon(String method) {
    switch (method) {
      case 'cash':
        return Icons.money;
      case 'card':
        return Icons.credit_card;
      case 'pix':
        return Icons.qr_code;
      default:
        return Icons.payment;
    }
  }

  String _getPaymentName(String method) {
    switch (method) {
      case 'cash':
        return 'Dinheiro';
      case 'card':
        return 'Cartão';
      case 'pix':
        return 'PIX';
      default:
        return method;
    }
  }

  Widget _buildRevenueChart() {
    final topServices = (_reports['topServices'] as List? ?? []);
    if (topServices.isEmpty) {
      return const Center(
        child: Text(
          'Sem dados para exibir',
          style: TextStyle(color: AppColors.textSecondary),
        ),
      );
    }

    return BarChart(
      BarChartData(
        alignment: BarChartAlignment.spaceAround,
        maxY: topServices.isNotEmpty
            ? (topServices
                      .map((s) => _parseAmount(s['revenue']))
                      .reduce((a, b) => a > b ? a : b) *
                  1.2)
            : 100,
        barTouchData: BarTouchData(
          enabled: true,
          touchTooltipData: BarTouchTooltipData(
            getTooltipColor: (group) => AppColors.primaryGold.withOpacity(0.9),
            getTooltipItem: (group, groupIndex, rod, rodIndex) {
              return BarTooltipItem(
                'R\$ ${rod.toY.toStringAsFixed(2).replaceAll('.', ',')}',
                const TextStyle(
                  color: AppColors.black,
                  fontWeight: FontWeight.bold,
                ),
              );
            },
          ),
        ),
        titlesData: FlTitlesData(
          show: true,
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 40,
              getTitlesWidget: (value, meta) {
                if (value.toInt() >= 0 && value.toInt() < topServices.length) {
                  final service = topServices[value.toInt()];
                  final name = service['name'].toString();
                  return Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(
                      name.length > 10 ? '${name.substring(0, 10)}...' : name,
                      style: const TextStyle(
                        fontSize: 10,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  );
                }
                return const Text('');
              },
            ),
          ),
          leftTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 50,
              getTitlesWidget: (value, meta) {
                return Text(
                  'R\$ ${value.toInt()}',
                  style: const TextStyle(
                    fontSize: 10,
                    color: AppColors.textSecondary,
                  ),
                );
              },
            ),
          ),
          topTitles: const AxisTitles(
            sideTitles: SideTitles(showTitles: false),
          ),
          rightTitles: const AxisTitles(
            sideTitles: SideTitles(showTitles: false),
          ),
        ),
        gridData: FlGridData(
          show: true,
          drawVerticalLine: false,
          horizontalInterval: 50,
          getDrawingHorizontalLine: (value) {
            return FlLine(
              color: AppColors.textSecondary.withOpacity(0.1),
              strokeWidth: 1,
            );
          },
        ),
        borderData: FlBorderData(show: false),
        barGroups: topServices.asMap().entries.map((entry) {
          final index = entry.key;
          final service = entry.value;
          return BarChartGroupData(
            x: index,
            barRods: [
              BarChartRodData(
                toY: _parseAmount(service['revenue']),
                color: AppColors.primaryGold,
                width: 20,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(6),
                  topRight: Radius.circular(6),
                ),
              ),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildPaymentMethodsChart() {
    final paymentMethods = (_reports['paymentMethods'] as List? ?? []);
    if (paymentMethods.isEmpty) {
      return const Center(
        child: Text(
          'Sem dados para exibir',
          style: TextStyle(color: AppColors.textSecondary),
        ),
      );
    }

    final total = paymentMethods.fold<double>(
      0,
      (sum, method) => sum + _parseAmount(method['total']),
    );

    return Row(
      children: [
        Expanded(
          flex: 2,
          child: PieChart(
            PieChartData(
              sectionsSpace: 2,
              centerSpaceRadius: 40,
              sections: paymentMethods.map((method) {
                final value = _parseAmount(method['total']);
                final percentage = (value / total * 100);
                return PieChartSectionData(
                  color: _getPaymentColor(method['payment_method']),
                  value: value,
                  title: '${percentage.toStringAsFixed(1)}%',
                  radius: 60,
                  titleStyle: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                );
              }).toList(),
            ),
          ),
        ),
        Expanded(
          flex: 1,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: paymentMethods.map((method) {
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  children: [
                    Container(
                      width: 16,
                      height: 16,
                      decoration: BoxDecoration(
                        color: _getPaymentColor(method['payment_method']),
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _getPaymentName(method['payment_method']),
                        style: const TextStyle(fontSize: 12),
                      ),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  Color _getPaymentColor(String method) {
    switch (method) {
      case 'cash':
        return AppColors.success;
      case 'card':
        return AppColors.info;
      case 'pix':
        return AppColors.primaryGold;
      default:
        return AppColors.textSecondary;
    }
  }
}
