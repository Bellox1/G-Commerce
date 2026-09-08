import React from 'react';
import { ScrollView, KeyboardAvoidingView, Platform } from 'react-native';

const KeyboardAwareScrollView = (props) => (
  <KeyboardAvoidingView
    behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    style={{ flex: 1 }}
    keyboardVerticalOffset={Platform.OS === 'ios' ? 64 : 0}
  >
    <ScrollView keyboardShouldPersistTaps="handled" {...props} />
  </KeyboardAvoidingView>
);

export default KeyboardAwareScrollView;
