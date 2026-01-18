export interface Book {
  id: string;
  title: string;
  author: string;
  year: number;
  score: number;
  tags: string[];
  hook: string;
  coverGradient: string;
}

export interface Author {
  id: string;
  name: string;
  starterBook: string;
  style: string;
  avatarColor: string;
}

export interface Category {
  name: string;
  count: number;
  icon: string;
}

export interface Collection {
  id: string;
  title: string;
  count: number;
  hook: string;
  colors: string[];
}

export const books: Book[] = [
  { id: '1', title: 'العادات الذرية', author: 'جيمس كلير', year: 2018, score: 9.2, tags: ['تطوير ذات', 'إنتاجية'], hook: 'تغييرات صغيرة، نتائج مذهلة', coverGradient: 'from-teal-400 to-teal-600' },
  { id: '2', title: 'أولاد حارتنا', author: 'نجيب محفوظ', year: 1959, score: 9.5, tags: ['رواية', 'كلاسيك'], hook: 'رحلة الإنسانية في حارة واحدة', coverGradient: 'from-amber-400 to-orange-500' },
  { id: '3', title: 'مئة عام من العزلة', author: 'غابرييل غارسيا ماركيز', year: 1967, score: 9.4, tags: ['رواية', 'واقعية سحرية'], hook: 'ملحمة عائلة بوينديا الأسطورية', coverGradient: 'from-emerald-400 to-green-600' },
  { id: '4', title: 'الأب الغني والأب الفقير', author: 'روبرت كيوساكي', year: 1997, score: 8.7, tags: ['مال', 'استثمار'], hook: 'دروس في الثروة لم تتعلمها في المدرسة', coverGradient: 'from-blue-400 to-indigo-600' },
  { id: '5', title: 'فن اللامبالاة', author: 'مارك مانسون', year: 2016, score: 8.4, tags: ['تطوير ذات', 'فلسفة'], hook: 'كيف تعيش حياة جيدة بالتوقف عن المحاولة', coverGradient: 'from-rose-400 to-pink-600' },
  { id: '6', title: 'ثلاثية غرناطة', author: 'رضوى عاشور', year: 1994, score: 9.3, tags: ['رواية', 'تاريخ'], hook: 'سقوط الأندلس بعيون عائلة واحدة', coverGradient: 'from-purple-400 to-violet-600' },
  { id: '7', title: 'الخيميائي', author: 'باولو كويلو', year: 1988, score: 8.9, tags: ['رواية', 'فلسفة'], hook: 'اتبع حلمك واكتشف أسطورتك الشخصية', coverGradient: 'from-yellow-400 to-amber-500' },
  { id: '8', title: 'كتاب الأيام', author: 'طه حسين', year: 1929, score: 9.1, tags: ['سيرة ذاتية', 'كلاسيك'], hook: 'سيرة عميد الأدب العربي', coverGradient: 'from-slate-400 to-slate-600' },
  { id: '9', title: 'موسم الهجرة إلى الشمال', author: 'الطيب صالح', year: 1966, score: 9.0, tags: ['رواية', 'كلاسيك'], hook: 'صراع الهوية بين الشرق والغرب', coverGradient: 'from-cyan-400 to-teal-600' },
  { id: '10', title: 'في قلبي أنثى عبرية', author: 'خولة حمدي', year: 2012, score: 8.5, tags: ['رواية', 'معاصر'], hook: 'قصة حب تتجاوز الحدود', coverGradient: 'from-red-400 to-rose-600' },
  { id: '11', title: 'عزازيل', author: 'يوسف زيدان', year: 2008, score: 9.0, tags: ['رواية', 'تاريخ'], hook: 'راهب مصري في صراع الإيمان والشك', coverGradient: 'from-orange-400 to-red-500' },
  { id: '12', title: 'ذاكرة الجسد', author: 'أحلام مستغانمي', year: 1993, score: 8.8, tags: ['رواية', 'رومانسي'], hook: 'الحب والوطن في قلب الجزائر', coverGradient: 'from-pink-400 to-fuchsia-600' },
];

export const authors: Author[] = [
  { id: '1', name: 'نجيب محفوظ', starterBook: 'الثلاثية', style: 'كلاسيكي', avatarColor: 'bg-amber-500' },
  { id: '2', name: 'غادة السمان', starterBook: 'بيروت 75', style: 'حداثي', avatarColor: 'bg-rose-500' },
  { id: '3', name: 'محمود درويش', starterBook: 'أثر الفراشة', style: 'شعري', avatarColor: 'bg-teal-500' },
  { id: '4', name: 'أحلام مستغانمي', starterBook: 'ذاكرة الجسد', style: 'رومانسي', avatarColor: 'bg-purple-500' },
  { id: '5', name: 'يوسف زيدان', starterBook: 'عزازيل', style: 'تاريخي', avatarColor: 'bg-blue-500' },
  { id: '6', name: 'جبران خليل جبران', starterBook: 'النبي', style: 'فلسفي', avatarColor: 'bg-emerald-500' },
];

export const categories: Category[] = [
  { name: 'روايات', count: 1250, icon: '📖' },
  { name: 'تطوير ذات', count: 890, icon: '🚀' },
  { name: 'تاريخ', count: 654, icon: '🏛️' },
  { name: 'فلسفة', count: 432, icon: '🧠' },
  { name: 'سيرة ذاتية', count: 321, icon: '📝' },
  { name: 'علم نفس', count: 567, icon: '💭' },
  { name: 'اقتصاد ومال', count: 289, icon: '💰' },
  { name: 'شعر', count: 445, icon: '✨' },
  { name: 'أدب كلاسيكي', count: 678, icon: '📚' },
  { name: 'خيال علمي', count: 234, icon: '🔬' },
];

export const collections: Collection[] = [
  { id: '1', title: 'كتب غيّرت حياتي', count: 15, hook: 'قراء يشاركون الكتب التي غيّرتهم', colors: ['bg-teal-500', 'bg-teal-400', 'bg-teal-300'] },
  { id: '2', title: 'للمبتدئين في القراءة', count: 20, hook: 'كتب سهلة وممتعة للبداية', colors: ['bg-amber-500', 'bg-amber-400', 'bg-amber-300'] },
  { id: '3', title: 'روايات لا تُنسى', count: 25, hook: 'روايات ستبقى معك للأبد', colors: ['bg-rose-500', 'bg-rose-400', 'bg-rose-300'] },
  { id: '4', title: 'كتب قصيرة قوية', count: 18, hook: 'أقل من 200 صفحة، أثر كبير', colors: ['bg-blue-500', 'bg-blue-400', 'bg-blue-300'] },
  { id: '5', title: 'فكر وفلسفة', count: 12, hook: 'للتأمل والتفكير العميق', colors: ['bg-purple-500', 'bg-purple-400', 'bg-purple-300'] },
  { id: '6', title: 'كتب ريادة أعمال', count: 22, hook: 'للطموحين ورواد الأعمال', colors: ['bg-emerald-500', 'bg-emerald-400', 'bg-emerald-300'] },
];

export const trendingSearches: string[] = [
  'العادات الذرية',
  'نجيب محفوظ',
  'كتب عن القلق',
  'روايات رومانسية',
  'تطوير الذات',
  'الأدب الروسي',
];

export const latestBooks: { title: string; author: string }[] = [
  { title: 'الغريب', author: 'ألبير كامو' },
  { title: 'البؤساء', author: 'فيكتور هوغو' },
  { title: 'الجريمة والعقاب', author: 'دوستويفسكي' },
  { title: 'كبرياء وتحامل', author: 'جين أوستن' },
  { title: 'الأمير الصغير', author: 'أنطوان دو سانت' },
  { title: '1984', author: 'جورج أورويل' },
  { title: 'مزرعة الحيوان', author: 'جورج أورويل' },
  { title: 'الحرب والسلام', author: 'تولستوي' },
  { title: 'آنا كارنينا', author: 'تولستوي' },
  { title: 'دون كيخوته', author: 'سرفانتس' },
];

export const trendingNow: string[] = [
  'أفضل روايات 2024',
  'كتب التنمية البشرية',
  'روايات قصيرة',
  'كتب فلسفة للمبتدئين',
  'أدب نجيب محفوظ',
  'كتب عن الثقة بالنفس',
  'روايات بوليسية',
  'كتب عربية حديثة',
  'سيرة ذاتية ملهمة',
  'كتب إسلامية',
];

export const quote = {
  text: 'إن الكتب التي يحتاجها الناس هي الكتب التي تجعلهم يفكرون، والكتب التي تجعلهم يفكرون هي الكتب التي يحتاجون إليها أكثر من غيرها.',
  author: 'عباس محمود العقاد',
  book: 'ساعات بين الكتب',
};

export const searchPlaceholders = [
  'ابحث عن: العادات الذرية',
  'ابحث عن: نجيب محفوظ',
  'ابحث عن: كتب عن القلق',
  'ابحث عن: روايات قصيرة',
];
