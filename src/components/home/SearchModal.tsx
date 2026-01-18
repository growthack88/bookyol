import { useState, useEffect, useRef } from 'react';
import { Search, X } from 'lucide-react';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { books, authors, trendingSearches, searchPlaceholders } from '@/data/mockData';

interface SearchModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const SearchModal = ({ isOpen, onClose }: SearchModalProps) => {
  const [query, setQuery] = useState('');
  const [placeholderIndex, setPlaceholderIndex] = useState(0);
  const inputRef = useRef<HTMLInputElement>(null);
  const debouncedQuery = useDebouncedValue(query, 200);

  // Focus input on open
  useEffect(() => {
    if (isOpen && inputRef.current) {
      inputRef.current.focus();
    }
  }, [isOpen]);

  // Handle escape key
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };
    if (isOpen) {
      document.addEventListener('keydown', handleKeyDown);
      document.body.style.overflow = 'hidden';
    }
    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.body.style.overflow = '';
    };
  }, [isOpen, onClose]);

  // Rotate placeholders
  useEffect(() => {
    const interval = setInterval(() => {
      setPlaceholderIndex((prev) => (prev + 1) % searchPlaceholders.length);
    }, 3000);
    return () => clearInterval(interval);
  }, []);

  const filteredBooks = debouncedQuery
    ? books.filter(b => b.title.includes(debouncedQuery) || b.author.includes(debouncedQuery)).slice(0, 5)
    : [];

  const filteredAuthors = debouncedQuery
    ? authors.filter(a => a.name.includes(debouncedQuery)).slice(0, 3)
    : [];

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm pt-20">
      <div
        className="w-full max-w-2xl mx-4 animate-scale-in"
        role="dialog"
        aria-modal="true"
        aria-label="البحث"
      >
        <div className="rounded-2xl border border-border bg-card shadow-2xl overflow-hidden">
          {/* Search Input */}
          <div className="flex items-center border-b border-border px-4">
            <Search className="h-5 w-5 text-muted-foreground" />
            <input
              ref={inputRef}
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder={searchPlaceholders[placeholderIndex]}
              className="flex-1 bg-transparent py-4 px-3 text-lg outline-none placeholder:text-muted-foreground/60"
            />
            <button
              onClick={onClose}
              className="p-2 text-muted-foreground hover:text-foreground transition-colors"
              aria-label="إغلاق"
            >
              <X className="h-5 w-5" />
            </button>
          </div>

          {/* Results or Trending */}
          <div className="max-h-96 overflow-y-auto p-4">
            {debouncedQuery ? (
              <>
                {filteredBooks.length > 0 && (
                  <div className="mb-4">
                    <p className="mb-2 text-xs font-medium text-muted-foreground">كتب</p>
                    {filteredBooks.map((book) => (
                      <button
                        key={book.id}
                        className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-right transition-colors hover:bg-accent"
                      >
                        <div className={`h-12 w-8 rounded bg-gradient-to-br ${book.coverGradient}`} />
                        <div>
                          <p className="font-medium text-foreground">{book.title}</p>
                          <p className="text-sm text-muted-foreground">{book.author}</p>
                        </div>
                      </button>
                    ))}
                  </div>
                )}
                {filteredAuthors.length > 0 && (
                  <div>
                    <p className="mb-2 text-xs font-medium text-muted-foreground">مؤلفون</p>
                    {filteredAuthors.map((author) => (
                      <button
                        key={author.id}
                        className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-right transition-colors hover:bg-accent"
                      >
                        <div className={`flex h-10 w-10 items-center justify-center rounded-full ${author.avatarColor} text-sm font-bold text-white`}>
                          {author.name.charAt(0)}
                        </div>
                        <span className="font-medium text-foreground">{author.name}</span>
                      </button>
                    ))}
                  </div>
                )}
                {filteredBooks.length === 0 && filteredAuthors.length === 0 && (
                  <p className="py-8 text-center text-muted-foreground">لا توجد نتائج</p>
                )}
              </>
            ) : (
              <div>
                <p className="mb-3 text-xs font-medium text-muted-foreground">الأكثر بحثًا</p>
                <div className="flex flex-wrap gap-2">
                  {trendingSearches.map((term) => (
                    <button
                      key={term}
                      onClick={() => setQuery(term)}
                      className="rounded-full border border-border px-3 py-1.5 text-sm transition-all hover:border-primary/30 hover:bg-accent hover:scale-[1.03]"
                    >
                      {term}
                    </button>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default SearchModal;
