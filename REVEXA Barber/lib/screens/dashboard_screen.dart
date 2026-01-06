import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import '../theme/app_colors.dart';
import '../services/api_service.dart';
import '../widgets/stat_card.dart';
import '../widgets/responsive_layout.dart';
import '../widgets/skeleton_loader.dart';
import '../providers/auth_provider.dart';
import 'package:provider/provider.dart';
import 'clients_screen.dart';
import 'services_screen.dart';
import 'appointments_screen.dart';
import 'barbers_screen.dart';
import 'reports_screen.dart';
import 'settings_screen.dart';
import 'whatsapp_config_screen.dart';
import 'login_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService _api = ApiService();
  Map<String, dynamic> _dashboardData = {};
  List<FlSpot> _weeklyData = [];
  bool _isLoading = true;
  int _selectedIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
    _loadWeeklyData();
  }

  Future<void> _loadDashboard() async {
    setState(() => _isLoading = true);
    try {
      final data = await _api.getDashboard();
      await _loadWeeklyData();
      setState(() {
        _dashboardData = data;
        _isLoading = false;
      });
    } catch (e) {
      print('Error loading dashboard: $e'); // Debug
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erro ao carregar: ${e.toString()}'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
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

  Future<void> _loadWeeklyData() async {
    try {
      final spots = <FlSpot>[];
      final now = DateTime.now();

      for (int i = 6; i >= 0; i--) {
        final date = now.subtract(Duration(days: i));
        final dateStr =
            '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';

        try {
          final data = await _api.getAppointments(dateStr);
          double dayRevenue = 0;
          for (var apt in data) {
            if (apt['status'] == 'completed' && apt['price'] != null) {
              dayRevenue += _parseAmount(apt['price']);
            }
          }
          spots.add(FlSpot((6 - i).toDouble(), dayRevenue));
        } catch (e) {
          spots.add(FlSpot((6 - i).toDouble(), 0));
        }
      }

      setState(() {
        _weeklyData = spots;
      });
    } catch (e) {
      // Keep empty data
    }
  }

  Widget _getSelectedScreen() {
    switch (_selectedIndex) {
      case 0:
        return _buildDashboard();
      case 1:
        return const ClientsScreen();
      case 2:
        return const ServicesScreen();
      case 3:
        return const BarbersScreen();
      case 4:
        return const AppointmentsScreen();
      case 5:
        return const ReportsScreen();
      case 6:
        return const SettingsScreen();
      case 7:
        return const WhatsAppConfigScreen();
      default:
        return _buildDashboard();
    }
  }

  Widget _buildDashboard() {
    if (_isLoading) {
      return const SkeletonDashboard();
    }

    return RefreshIndicator(
      onRefresh: _loadDashboard,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Início',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 24),

            ResponsiveLayout(
              mobile: _buildStatsGrid(2),
              tablet: _buildStatsGrid(3),
              desktop: _buildStatsGrid(4),
            ),

            const SizedBox(height: 32),

            // Weekly trend chart
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Faturamento dos Últimos 7 Dias',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(height: 200, child: _buildWeeklyChart()),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Quick stats row
            Row(
              children: [
                Expanded(
                  child: Card(
                    color: AppColors.success.withOpacity(0.1),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          const Icon(
                            Icons.trending_up,
                            color: AppColors.success,
                            size: 32,
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Taxa de Ocupação',
                            style: TextStyle(
                              color: AppColors.textSecondary,
                              fontSize: 12,
                            ),
                          ),
                          Text(
                            '${(_parseAmount(_dashboardData['todayAppointments']) * 12.5).toStringAsFixed(1)}%',
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: AppColors.success,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Card(
                    color: AppColors.primaryGold.withOpacity(0.1),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          const Icon(
                            Icons.monetization_on,
                            color: AppColors.primaryGold,
                            size: 32,
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Ticket Médio',
                            style: TextStyle(
                              color: AppColors.textSecondary,
                              fontSize: 12,
                            ),
                          ),
                          Text(
                            'R\$ ${_calculateTicketMedio()}',
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: AppColors.primaryGold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildWeeklyChart() {
    if (_weeklyData.isEmpty) {
      return const Center(
        child: Text(
          'Carregando dados...',
          style: TextStyle(color: AppColors.textSecondary),
        ),
      );
    }

    final maxY = _weeklyData.map((e) => e.y).reduce((a, b) => a > b ? a : b);

    return LineChart(
      LineChartData(
        minY: 0,
        maxY: maxY > 0 ? maxY * 1.2 : 100,
        gridData: FlGridData(
          show: true,
          drawVerticalLine: false,
          horizontalInterval: maxY > 0 ? maxY / 4 : 25,
          getDrawingHorizontalLine: (value) {
            return FlLine(
              color: AppColors.textSecondary.withOpacity(0.1),
              strokeWidth: 1,
            );
          },
        ),
        titlesData: FlTitlesData(
          show: true,
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 32,
              getTitlesWidget: (value, meta) {
                if (value.toInt() < 0 || value.toInt() >= 7)
                  return const SizedBox();

                final days = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                final now = DateTime.now();
                final date = now.subtract(Duration(days: 6 - value.toInt()));
                final dayIndex = date.weekday % 7;

                return Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(
                    days[dayIndex],
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textSecondary,
                    ),
                  ),
                );
              },
            ),
          ),
          leftTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 45,
              getTitlesWidget: (value, meta) {
                return Text(
                  'R\$${value.toInt()}',
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
        borderData: FlBorderData(show: false),
        lineBarsData: [
          LineChartBarData(
            spots: _weeklyData,
            isCurved: true,
            color: AppColors.primaryGold,
            barWidth: 3,
            isStrokeCapRound: true,
            dotData: FlDotData(
              show: true,
              getDotPainter: (spot, percent, barData, index) {
                return FlDotCirclePainter(
                  radius: 4,
                  color: AppColors.primaryGold,
                  strokeWidth: 2,
                  strokeColor: AppColors.black,
                );
              },
            ),
            belowBarData: BarAreaData(
              show: true,
              color: AppColors.primaryGold.withOpacity(0.1),
            ),
          ),
        ],
      ),
    );
  }

  String _calculateTicketMedio() {
    final revenue = _parseAmount(_dashboardData['todayRevenue']);
    final appointments = _parseAmount(_dashboardData['todayAppointments']);
    if (appointments == 0) return '0,00';
    final avg = revenue / appointments;
    return avg.toStringAsFixed(2).replaceAll('.', ',');
  }

  Widget _buildStatsGrid(int crossAxisCount) {
    return GridView.count(
      crossAxisCount: crossAxisCount,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 16,
      mainAxisSpacing: 16,
      childAspectRatio: 1.5,
      children: [
        StatCard(
          title: 'Agendamentos Hoje',
          value: _dashboardData['todayAppointments']?.toString() ?? '0',
          icon: Icons.calendar_today,
          color: AppColors.info,
          onTap: () => setState(() => _selectedIndex = 4),
        ),
        StatCard(
          title: 'Faturamento Hoje',
          value:
              'R\$ ${_parseAmount(_dashboardData['todayRevenue']).toStringAsFixed(2)}',
          icon: Icons.attach_money,
          color: AppColors.success,
        ),
        StatCard(
          title: 'Clientes Ativos',
          value: _dashboardData['activeClients']?.toString() ?? '0',
          icon: Icons.people,
          color: AppColors.primaryGold,
          onTap: () => setState(() => _selectedIndex = 1),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDesktop = ResponsiveLayout.isDesktop(context);
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                gradient: AppColors.goldGradient,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(
                Icons.content_cut,
                color: AppColors.black,
                size: 24,
              ),
            ),
            const SizedBox(width: 12),
            const Text('REVEXA'),
          ],
        ),
        actions: [
          if (isDesktop) ...[
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 0),
              icon: const Icon(Icons.home, color: AppColors.textPrimary),
              label: const Text(
                'Início',
                style: TextStyle(color: AppColors.textPrimary),
              ),
            ),
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 1),
              icon: const Icon(Icons.people, color: AppColors.textPrimary),
              label: const Text(
                'Clientes',
                style: TextStyle(color: AppColors.textPrimary),
              ),
            ),
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 2),
              icon: const Icon(Icons.cut, color: AppColors.textPrimary),
              label: const Text(
                'Serviços',
                style: TextStyle(color: AppColors.textPrimary),
              ),
            ),
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 3),
              icon: const Icon(
                Icons.person_outline,
                color: AppColors.textPrimary,
              ),
              label: const Text(
                'Barbeiros',
                style: TextStyle(color: AppColors.textPrimary),
              ),
            ),
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 4),
              icon: const Icon(
                Icons.calendar_month,
                color: AppColors.textPrimary,
              ),
              label: const Text(
                'Agendamentos',
                style: TextStyle(color: AppColors.textPrimary),
              ),
            ),
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 5),
              icon: const Icon(Icons.analytics, color: AppColors.textPrimary),
              label: const Text(
                'Relatórios',
                style: TextStyle(color: AppColors.textPrimary),
              ),
            ),
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 6),
              icon: const Icon(Icons.settings, color: AppColors.textPrimary),
              label: const Text(
                'Configurações',
                style: TextStyle(color: AppColors.textPrimary),
              ),
            ),
            TextButton.icon(
              onPressed: () => setState(() => _selectedIndex = 7),
              icon: const Icon(Icons.chat, color: AppColors.primaryGold),
              label: const Text(
                'WhatsApp',
                style: TextStyle(color: AppColors.primaryGold),
              ),
            ),
          ],
          PopupMenuButton<void>(
            icon: const Icon(Icons.account_circle, size: 32),
            itemBuilder: (context) => <PopupMenuEntry<void>>[
              PopupMenuItem(
                child: ListTile(
                  leading: const Icon(Icons.person),
                  title: Text(authProvider.user?.fullName ?? ''),
                  subtitle: Text(authProvider.user?.username ?? ''),
                ),
              ),
              const PopupMenuDivider(),
              PopupMenuItem(
                onTap: () async {
                  await authProvider.logout();
                  if (context.mounted) {
                    Navigator.of(context).pushReplacement(
                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                    );
                  }
                },
                child: const ListTile(
                  leading: Icon(Icons.logout, color: AppColors.error),
                  title: Text('Sair'),
                ),
              ),
            ],
          ),
          const SizedBox(width: 8),
        ],
      ),
      drawer: !isDesktop
          ? Drawer(
              child: ListView(
                children: [
                  DrawerHeader(
                    decoration: const BoxDecoration(
                      gradient: AppColors.goldGradient,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(
                          Icons.content_cut,
                          size: 48,
                          color: AppColors.black,
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'REVEXA',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: AppColors.black,
                          ),
                        ),
                        Text(
                          authProvider.user?.fullName ?? '',
                          style: const TextStyle(
                            color: AppColors.black,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
                  ),
                  ListTile(
                    leading: const Icon(Icons.dashboard),
                    title: const Text('Dashboard'),
                    selected: _selectedIndex == 0,
                    onTap: () {
                      setState(() => _selectedIndex = 0);
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.people),
                    title: const Text('Clientes'),
                    selected: _selectedIndex == 1,
                    onTap: () {
                      setState(() => _selectedIndex = 1);
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.cut),
                    title: const Text('Serviços'),
                    selected: _selectedIndex == 2,
                    onTap: () {
                      setState(() => _selectedIndex = 2);
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.person_outline),
                    title: const Text('Barbeiros'),
                    selected: _selectedIndex == 3,
                    onTap: () {
                      setState(() => _selectedIndex = 3);
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.calendar_month),
                    title: const Text('Agendamentos'),
                    selected: _selectedIndex == 4,
                    onTap: () {
                      setState(() => _selectedIndex = 4);
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.analytics),
                    title: const Text('Relatórios'),
                    selected: _selectedIndex == 5,
                    onTap: () {
                      setState(() => _selectedIndex = 5);
                      Navigator.pop(context);
                    },
                  ),
                  const Divider(),
                  ListTile(
                    leading: const Icon(Icons.settings),
                    title: const Text('Configurações'),
                    selected: _selectedIndex == 6,
                    onTap: () {
                      setState(() => _selectedIndex = 6);
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(
                      Icons.chat,
                      color: AppColors.primaryGold,
                    ),
                    title: const Text('WhatsApp'),
                    selected: _selectedIndex == 7,
                    onTap: () {
                      setState(() => _selectedIndex = 7);
                      Navigator.pop(context);
                    },
                  ),
                ],
              ),
            )
          : null,
      body: _getSelectedScreen(),
      bottomNavigationBar: !isDesktop ? _buildBottomNav() : null,
    );
  }

  Widget _buildBottomNav() {
    return BottomNavigationBar(
      currentIndex: _selectedIndex > 4 ? 0 : _selectedIndex,
      onTap: (index) => setState(() => _selectedIndex = index),
      type: BottomNavigationBarType.fixed,
      backgroundColor: AppColors.surfaceDark,
      selectedItemColor: AppColors.primaryGold,
      unselectedItemColor: AppColors.textSecondary,
      selectedLabelStyle: const TextStyle(
        fontSize: 13,
        fontWeight: FontWeight.bold,
      ),
      unselectedLabelStyle: const TextStyle(fontSize: 12),
      iconSize: 28,
      items: const [
        BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Início'),
        BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Clientes'),
        BottomNavigationBarItem(icon: Icon(Icons.cut), label: 'Serviços'),
        BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          label: 'Barbeiros',
        ),
        BottomNavigationBarItem(icon: Icon(Icons.event), label: 'Horários'),
      ],
    );
  }
}
