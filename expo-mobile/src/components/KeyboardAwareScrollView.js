import React from 'react';
import { ScrollView, KeyboardAvoidingView, Platform } from 'react-native';

const KeyboardAwareScrollView = ({ children, contentContainerStyle, ...props }) => (
  <KeyboardAvoidingView
    behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    style={{ flex: 1 }}
    keyboardVerticalOffset={Platform.OS === 'ios' ? 64 : 20}
  >
    <ScrollView
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={false}
      contentContainerStyle={contentContainerStyle}
      {...props}
    >
      {children}
    </ScrollView>
  </KeyboardAvoidingView>
);

export default KeyboardAwareScrollView;
