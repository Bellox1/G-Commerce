import React from 'react';
import { ScrollView, KeyboardAvoidingView, Platform } from 'react-native';

const KeyboardAwareScrollView = (props) => (
  <KeyboardAvoidingView
    behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    style={{ flex: 1 }}
    keyboardVerticalOffset={0}
  >
    <ScrollView keyboardShouldPersistTaps="handled" {...props} />
  </KeyboardAvoidingView>
);

export default KeyboardAwareScrollView;
