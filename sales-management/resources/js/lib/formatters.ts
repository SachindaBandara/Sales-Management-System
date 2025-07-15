// src/utils/formatters.ts
export const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};

export const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

export const formatStatusLabel = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};

export const getStatusVariant = (
  status: string,
): 'secondary' | 'default' | 'destructive' | 'outline' => {
  const variants = {
    pending: 'secondary',
    processing: 'default',
    shipped: 'default',
    delivered: 'default',
    cancelled: 'destructive',
  } as const;
  return variants[status as keyof typeof variants] || 'outline';
};

export const getPaymentStatusVariant = (
  status: string,
): 'secondary' | 'default' | 'destructive' | 'outline' => {
  const variants = {
    pending: 'secondary',
    paid: 'default',
    failed: 'destructive',
    refunded: 'outline',
  } as const;
  return variants[status as keyof typeof variants] || 'outline';
};
