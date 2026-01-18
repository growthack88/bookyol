import { useState } from 'react';
import { Star, Bookmark, ExternalLink } from 'lucide-react';
import type { Book } from '@/data/mockData';

interface BookCardProps {
  book: Book;
  onSave: (id: string) => void;
  isSkeleton?: boolean;
}

const BookCard = ({ book, onSave, isSkeleton }: BookCardProps) => {
  const [isSaved, setIsSaved] = useState(false);

  const handleSave = () => {
    setIsSaved(!isSaved);
    onSave(book.id);
  };

  if (isSkeleton) {
    return (
      <div className="animate-pulse rounded-2xl border border-border bg-card p-4">
        <div className="mb-3 h-40 rounded-xl bg-muted" />
        <div className="mb-2 h-5 w-3/4 rounded bg-muted" />
        <div className="mb-3 h-4 w-1/2 rounded bg-muted" />
        <div className="mb-3 flex gap-2">
          <div className="h-6 w-16 rounded-full bg-muted" />
          <div className="h-6 w-16 rounded-full bg-muted" />
        </div>
        <div className="h-4 w-full rounded bg-muted" />
      </div>
    );
  }

  return (
    <article className="group relative overflow-hidden rounded-2xl border border-border bg-card transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
      {/* Cover */}
      <div className={`relative h-44 bg-gradient-to-br ${book.coverGradient} p-4`}>
        {/* Score badge */}
        <div className="absolute left-3 top-3 flex items-center gap-1 rounded-full bg-black/20 px-2 py-1 text-sm font-medium text-white backdrop-blur-sm">
          <Star className="h-3.5 w-3.5 fill-yellow-400 text-yellow-400" />
          {book.score}
        </div>
        {/* Year */}
        <div className="absolute bottom-3 right-3 rounded bg-black/20 px-2 py-0.5 text-xs text-white/90 backdrop-blur-sm">
          {book.year}
        </div>
      </div>

      {/* Content */}
      <div className="p-4">
        <h3 className="mb-1 text-base font-semibold text-foreground line-clamp-1 group-hover:text-primary transition-colors">
          {book.title}
        </h3>
        <p className="mb-3 text-sm text-muted-foreground">{book.author}</p>

        {/* Tags */}
        <div className="mb-3 flex flex-wrap gap-1.5">
          {book.tags.slice(0, 2).map((tag) => (
            <span
              key={tag}
              className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground transition-all hover:scale-[1.03]"
            >
              {tag}
            </span>
          ))}
        </div>

        {/* Hook line */}
        <p className="mb-4 text-sm text-muted-foreground line-clamp-2">
          {book.hook}
        </p>

        {/* Actions */}
        <div className="flex gap-2">
          <button className="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-primary py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
            <ExternalLink className="h-4 w-4" />
            افتح
          </button>
          <button
            onClick={handleSave}
            className={`flex items-center justify-center rounded-xl border px-3 py-2 transition-all hover:scale-105 ${
              isSaved
                ? 'border-primary bg-primary/10 text-primary'
                : 'border-border text-muted-foreground hover:border-primary/30 hover:text-foreground'
            }`}
            aria-label={isSaved ? 'إزالة من المحفوظات' : 'حفظ'}
          >
            <Bookmark className={`h-4 w-4 ${isSaved ? 'fill-current' : ''}`} />
          </button>
        </div>
      </div>
    </article>
  );
};

export default BookCard;
