import { useState, useEffect } from 'react';
import { books } from '@/data/mockData';
import BookCard from './BookCard';
import { useInView } from '@/hooks/useInView';
import Toast from './Toast';

const tabs = [
  { id: 'popular', label: 'الأكثر قراءة' },
  { id: 'rated', label: 'الأعلى تقييمًا' },
  { id: 'new', label: 'الجديد' },
  { id: 'short', label: 'قصير' },
];

const BookGrid = () => {
  const [activeTab, setActiveTab] = useState('popular');
  const [isLoading, setIsLoading] = useState(true);
  const [showToast, setShowToast] = useState(false);
  const [sectionRef, isInView] = useInView<HTMLElement>({ threshold: 0.1 });

  // Simulate loading
  useEffect(() => {
    setIsLoading(true);
    const timer = setTimeout(() => setIsLoading(false), 600);
    return () => clearTimeout(timer);
  }, [activeTab]);

  const handleSave = (id: string) => {
    setShowToast(true);
    setTimeout(() => setShowToast(false), 2500);
  };

  // Sort books based on active tab
  const sortedBooks = [...books].sort((a, b) => {
    if (activeTab === 'rated') return b.score - a.score;
    if (activeTab === 'new') return b.year - a.year;
    return 0;
  });

  return (
    <section
      ref={sectionRef}
      className={`py-12 transition-all duration-700 ${
        isInView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
      }`}
      aria-labelledby="featured-books-title"
    >
      <div className="container mx-auto px-4">
        {/* Header */}
        <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 id="featured-books-title" className="text-2xl font-bold text-foreground">
              كتب مميزة
            </h2>
            <p className="text-muted-foreground">اكتشف أفضل الكتب المختارة لك</p>
          </div>

          {/* Tabs */}
          <div className="flex gap-2 overflow-x-auto pb-2 sm:pb-0">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`shrink-0 rounded-full px-4 py-2 text-sm font-medium transition-all hover:scale-[1.03] ${
                  activeTab === tab.id
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-muted text-muted-foreground hover:bg-muted/80'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        {/* Grid */}
        <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          {isLoading
            ? Array.from({ length: 8 }).map((_, i) => (
                <BookCard
                  key={i}
                  book={books[0]}
                  onSave={() => {}}
                  isSkeleton
                />
              ))
            : sortedBooks.map((book, index) => (
                <div
                  key={book.id}
                  className="animate-fade-in"
                  style={{ animationDelay: `${index * 50}ms` }}
                >
                  <BookCard book={book} onSave={handleSave} />
                </div>
              ))}
        </div>
      </div>

      <Toast
        message="تم الحفظ في قائمة القراءة"
        isVisible={showToast}
        onClose={() => setShowToast(false)}
      />
    </section>
  );
};

export default BookGrid;
