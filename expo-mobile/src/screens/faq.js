import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../theme/Colors';
import FAQ_DATA from '../data/faqData';
import TopHeaderNav from '../components/TopHeaderNav';

const BADGE_COLORS = {
  success: { bg: '#dcfce7', fg: '#166534' },
  warning: { bg: '#fef3c7', fg: '#92400e' },
  danger: { bg: '#fee2e2', fg: '#991b1b' },
  gray: { bg: '#f1f5f9', fg: '#475569' },
  info: { bg: '#dbeafe', fg: '#1e40af' },
};

const INLINE_RE = /(\*\*[^*]+\*\*|\[badge:[a-z]+:[^\]]+\])/g;

const BadgePill = ({ type, label }) => {
  const c = BADGE_COLORS[type] || BADGE_COLORS.gray;
  return (
    <View style={[styles.badge, { backgroundColor: c.bg }]}>
      <Text style={[styles.badgeText, { color: c.fg }]}>{label}</Text>
    </View>
  );
};

const renderInline = (text, kp) => {
  const parts = text.split(INLINE_RE).filter(Boolean);
  return parts.map((part, i) => {
    if (part.startsWith('**') && part.endsWith('**')) {
      return <Text key={kp + i} style={styles.bold}>{part.slice(2, -2)}</Text>;
    }
    const m = part.match(/^\[badge:([a-z]+):([^\]]+)\]$/);
    if (m) {
      return <BadgePill key={kp + i} type={m[1]} label={m[2]} />;
    }
    return <Text key={kp + i}>{part}</Text>;
  });
};

const renderBlock = (block, idx) => {
  switch (block.t) {
    case 'p':
      return <Text key={idx} style={styles.p}>{renderInline(block.text, 'p' + idx)}</Text>;
    case 'ul':
      return (
        <View key={idx} style={styles.list}>
          {block.items.map((it, i) => (
            <View key={i} style={styles.li}>
              <Text style={styles.bullet}>•</Text>
              <Text style={styles.liText}>{renderInline(it, 'ul' + idx + '_' + i)}</Text>
            </View>
          ))}
        </View>
      );
    case 'ol':
      return (
        <View key={idx} style={styles.list}>
          {block.items.map((it, i) => (
            <View key={i} style={styles.li}>
              <Text style={styles.olNum}>{i + 1}.</Text>
              <Text style={styles.liText}>{renderInline(it, 'ol' + idx + '_' + i)}</Text>
            </View>
          ))}
        </View>
      );
    case 'steps':
      return (
        <View key={idx} style={styles.steps}>
          {block.steps.map((s, i) => (
            <View key={i} style={styles.step}>
              <View style={styles.stepNum}><Text style={styles.stepNumText}>{s.num}</Text></View>
              <View style={styles.stepBody}>
                <Text style={styles.stepText}>{renderInline(s.text, 'st' + idx + '_' + i)}</Text>
                {s.items && s.items.map((it, j) => (
                  <View key={j} style={styles.li}>
                    <Text style={styles.bullet}>•</Text>
                    <Text style={styles.liText}>{renderInline(it, 'sti' + idx + '_' + i + '_' + j)}</Text>
                  </View>
                ))}
              </View>
            </View>
          ))}
        </View>
      );
    case 'tip':
      return (
        <View key={idx} style={styles.boxTip}>
          <Ionicons name="bulb-outline" size={18} color="#166534" style={styles.boxIcon} />
          <Text style={styles.boxText}>{renderInline(block.text, 'tip' + idx)}</Text>
        </View>
      );
    case 'warn':
      return (
        <View key={idx} style={styles.boxWarn}>
          <Ionicons name="warning-outline" size={18} color="#9a3412" style={styles.boxIcon} />
          <Text style={styles.boxText}>{renderInline(block.text, 'warn' + idx)}</Text>
        </View>
      );
    case 'info':
      return (
        <View key={idx} style={styles.boxInfo}>
          <Ionicons name="lock-closed-outline" size={18} color="#1e40af" style={styles.boxIcon} />
          <Text style={styles.boxText}>{renderInline(block.text, 'info' + idx)}</Text>
        </View>
      );
    default:
      return null;
  }
};

const HelpScreen = ({ navigation, route }) => {
  const initialTab = Math.max(0, FAQ_DATA.findIndex((t) => t.id === (route?.params?.tab || 'dashboard')));
  const [activeTab, setActiveTab] = useState(initialTab >= 0 ? initialTab : 0);
  const [openIndex, setOpenIndex] = useState(-1);

  const tab = FAQ_DATA[activeTab];

  return (
    <View style={styles.container}>
      {/* Top Navigation Header replacing Drawer */}
      <TopHeaderNav navigation={navigation} activeCategory="faq" />

      {/* View Title Header */}
      <View style={styles.viewHeaderRow}>
        <Text style={styles.headerTitle}>Centre d'aide</Text>
        <Text style={styles.headerSub}>Retrouvez ici tout ce qu'il faut savoir sur chaque module.</Text>
      </View>

      <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.tabsScroll} contentContainerStyle={styles.tabsContent}>
        {FAQ_DATA.map((t, i) => {
          const active = activeTab === i;
          return (
            <TouchableOpacity
              key={t.id}
              style={[styles.tab, active && styles.tabActive]}
              onPress={() => { setActiveTab(i); setOpenIndex(-1); }}
              activeOpacity={0.8}
            >
              <Ionicons name={t.icon} size={16} color={active ? Colors.primary : Colors.textLight} style={{ marginRight: 6 }} />
              <Text style={[styles.tabText, active && styles.tabTextActive]}>{t.label}</Text>
            </TouchableOpacity>
          );
        })}
      </ScrollView>

      <ScrollView showsVerticalScrollIndicator={false} style={styles.content} contentContainerStyle={styles.contentInner}>
        <Text style={styles.panelTitle}>{tab.title}</Text>

        {tab.sections.map((sec, i) => {
          const open = openIndex === i;
          return (
            <View key={i} style={styles.section}>
              <TouchableOpacity style={styles.qRow} onPress={() => setOpenIndex(open ? -1 : i)} activeOpacity={0.8}>
                <Text style={styles.qText}>{sec.q}</Text>
                <Ionicons name={open ? 'chevron-up-outline' : 'chevron-down-outline'} size={20} color={Colors.textLight} />
              </TouchableOpacity>
              {open && (
                <View style={styles.aBox}>
                  {sec.blocks.map((b, j) => renderBlock(b, j))}
                </View>
              )}
            </View>
          );
        })}

        {tab.notes && tab.notes.map((n, i) => renderBlock(n, 'note' + i))}

        <View style={styles.bottomSpacer} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  viewHeaderRow: {
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 8,
    backgroundColor: '#FFFFFF',
  },
  headerTitle: {
    fontSize: 20,
    fontFamily: 'SpaceGrotesk_700Bold',
    color: Colors.text,
  },
  headerSub: {
    fontSize: 12,
    fontFamily: 'PlusJakartaSans_400Regular',
    color: Colors.textLight,
    marginTop: 2,
  },
  tabsScroll: {
    maxHeight: 52,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  tabsContent: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    gap: 8,
    flexDirection: 'row',
  },
  tab: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: Colors.surface,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  tabActive: {
    backgroundColor: Colors.primaryLight,
    borderColor: Colors.primary,
  },
  tabText: {
    fontSize: 13,
    fontFamily: 'PlusJakartaSans_600SemiBold',
    color: Colors.textLight,
  },
  tabTextActive: {
    color: Colors.primary,
  },
  content: {
    flex: 1,
  },
  contentInner: {
    padding: 16,
  },
  panelTitle: {
    fontSize: 18,
    fontFamily: 'PlusJakartaSans_700Bold',
    color: Colors.text,
    marginBottom: 14,
    paddingBottom: 8,
    borderBottomWidth: 2,
    borderBottomColor: Colors.primary,
    alignSelf: 'flex-start',
  },
  section: {
    backgroundColor: Colors.surface,
    borderRadius: 12,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: Colors.border,
    overflow: 'hidden',
  },
  qRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 14,
    paddingHorizontal: 16,
  },
  qText: {
    flex: 1,
    fontSize: 15,
    fontFamily: 'PlusJakartaSans_600SemiBold',
    color: Colors.text,
    marginRight: 10,
  },
  aBox: {
    paddingHorizontal: 16,
    paddingBottom: 16,
    paddingTop: 2,
  },
  p: {
    fontSize: 14,
    fontFamily: 'PlusJakartaSans_400Regular',
    color: Colors.text,
    lineHeight: 22,
    marginBottom: 8,
  },
  bold: {
    fontFamily: 'PlusJakartaSans_700Bold',
  },
  list: {
    marginVertical: 6,
  },
  li: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 6,
  },
  bullet: {
    fontSize: 14,
    color: Colors.primary,
    marginRight: 8,
    lineHeight: 22,
    fontFamily: 'PlusJakartaSans_700Bold',
  },
  olNum: {
    fontSize: 14,
    color: Colors.primary,
    marginRight: 8,
    lineHeight: 22,
    fontFamily: 'PlusJakartaSans_700Bold',
    minWidth: 18,
  },
  liText: {
    flex: 1,
    fontSize: 14,
    fontFamily: 'PlusJakartaSans_400Regular',
    color: Colors.text,
    lineHeight: 22,
  },
  steps: {
    marginTop: 4,
  },
  step: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  stepNum: {
    width: 26,
    height: 26,
    borderRadius: 13,
    backgroundColor: Colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
    marginTop: 2,
  },
  stepNumText: {
    color: '#fff',
    fontSize: 12,
    fontFamily: 'PlusJakartaSans_700Bold',
  },
  stepBody: {
    flex: 1,
  },
  stepText: {
    fontSize: 14,
    fontFamily: 'PlusJakartaSans_400Regular',
    color: Colors.text,
    lineHeight: 22,
    marginBottom: 4,
  },
  boxTip: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#f0fdf4',
    borderWidth: 1,
    borderColor: '#bbf7d0',
    borderRadius: 8,
    padding: 12,
    marginTop: 10,
  },
  boxWarn: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#fff7ed',
    borderWidth: 1,
    borderColor: '#fed7aa',
    borderRadius: 8,
    padding: 12,
    marginTop: 10,
  },
  boxInfo: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#eff6ff',
    borderWidth: 1,
    borderColor: '#bfdbfe',
    borderRadius: 8,
    padding: 12,
    marginTop: 10,
  },
  boxIcon: {
    marginTop: 2,
    marginRight: 8,
  },
  boxText: {
    flex: 1,
    fontSize: 13,
    fontFamily: 'PlusJakartaSans_400Regular',
    lineHeight: 20,
  },
  badge: {
    alignSelf: 'flex-start',
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 2,
    marginRight: 6,
    marginTop: 2,
    marginBottom: 2,
  },
  badgeText: {
    fontSize: 12,
    fontFamily: 'PlusJakartaSans_600SemiBold',
  },
  bottomSpacer: {
    height: 30,
  },
});

export default HelpScreen;
