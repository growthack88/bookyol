import { Book, Bookmark, BookOpen, PenLine, FileText, Library, Glasses, NotebookPen, BookMarked, BookType, Quote, Highlighter } from "lucide-react";

const icons = [
  { Icon: Book, style: { top: "6%", left: "4%" }, animation: "animate-float-1", delay: "0s" },
  { Icon: Bookmark, style: { top: "12%", right: "6%" }, animation: "animate-float-2", delay: "1.2s" },
  { Icon: BookOpen, style: { bottom: "10%", left: "5%" }, animation: "animate-float-3", delay: "0.5s" },
  { Icon: PenLine, style: { top: "22%", left: "2%" }, animation: "animate-float-2", delay: "2s" },
  { Icon: FileText, style: { bottom: "18%", right: "4%" }, animation: "animate-float-1", delay: "0.8s" },
  { Icon: Library, style: { top: "4%", right: "18%" }, animation: "animate-float-3", delay: "1.5s" },
  { Icon: Glasses, style: { bottom: "6%", right: "12%" }, animation: "animate-float-2", delay: "2.2s" },
  { Icon: NotebookPen, style: { top: "38%", left: "1%" }, animation: "animate-float-1", delay: "0.3s" },
  { Icon: BookMarked, style: { bottom: "32%", right: "2%" }, animation: "animate-float-3", delay: "1.8s" },
  { Icon: BookType, style: { top: "3%", left: "22%" }, animation: "animate-float-2", delay: "2.5s" },
  { Icon: Quote, style: { bottom: "4%", left: "18%" }, animation: "animate-float-1", delay: "0.7s" },
  { Icon: Highlighter, style: { top: "52%", right: "1%" }, animation: "animate-float-3", delay: "1s" },
  { Icon: Book, style: { bottom: "22%", left: "1%" }, animation: "animate-float-2", delay: "1.7s" },
  { Icon: Bookmark, style: { top: "65%", right: "5%" }, animation: "animate-float-1", delay: "0.4s" },
  { Icon: BookOpen, style: { top: "8%", left: "10%" }, animation: "animate-float-3", delay: "2.8s" },
  { Icon: PenLine, style: { bottom: "38%", right: "6%" }, animation: "animate-float-2", delay: "1.3s" },
];

const FloatingIcons = () => {
  return (
    <div className="pointer-events-none fixed inset-0 overflow-hidden">
      {icons.map(({ Icon, style, animation, delay }, index) => (
        <div
          key={index}
          className={`absolute ${animation}`}
          style={{
            ...style,
            opacity: 0.08 + (index % 4) * 0.02,
            animationDelay: delay,
          }}
        >
          <Icon 
            className="text-primary" 
            size={18 + (index % 5) * 3} 
            strokeWidth={1}
          />
        </div>
      ))}
    </div>
  );
};

export default FloatingIcons;
